<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Outlet;
use App\Models\Tblog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller {

    public function index() {

        $outlets = Outlet::all();
        return view('reports.index', compact('outlets'));
    }

    public function generate(Request $request) {

        $request->validate([

            'jenis_laporan' => 'required|in:per_outlet,per_periode,per_status',
            'format' => 'required|in:view,pdf,excel'

        ]);

        $user = Auth::user();
        $jenisLaporan = $request->jenis_laporan;
        $format = $request->format;


        \Log::info('Generate report started', [

            'user' => $user->name,
            'jenis_laporan' => $jenisLaporan,
            'format' => $format

        ]);

        $query = Transaksi::with([

            'outlet',
            'member',
            'user',
            'detailTransaksi.paket'

        ]);

        switch ($jenisLaporan) {
            case 'per_outlet':

                if ($request->has('outlet_id') && $request->outlet_id) {

                    $query->where('id_outlet', $request->outlet_id);

                }

                $title = "Laporan Transaksi Per Outlet";

                break;

            case 'per_periode':
                $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
                $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

                $query->whereBetween('tgl', [$startDate, $endDate]);
                $title = "Laporan Transaksi Periode " . $startDate->format('d/m/Y') . " - " . $endDate->format('d/m/Y');

                break;

            case 'per_status':
                if ($request->has('status') && $request->status) {

                    $query->where('status', $request->status);

                }

                if ($request->has('dibayar') && $request->dibayar) {

                    $query->where('dibayar', $request->dibayar);

                }

                $title = "Laporan Transaksi Berdasarkan Status";

                break;

            default:
                $title = "Laporan Transaksi";

                break;

        }

        if (!$user->isOwner && $user->id_outlet) {

            $query->where('id_outlet', $user->id_outlet);

        }

        $transactions = $query->orderBy('tgl', 'desc')->get();

        \Log::info('Transactions fetched', ['count' => $transactions->count()]);

        $statistics = $this->calculateStatistics($transactions);

        $this->logActivity($user, "Generate laporan: {$jenisLaporan}", [

            'jenis_laporan' => $jenisLaporan,
            'format' => $format,
            'total_transaksi' => $transactions->count(),
            'total_pendapatan' => $statistics['total_pendapatan']

        ]);

        return $this->exportReport($format, $transactions, $title, $statistics, $request->all());

    }

    private function calculateStatistics($transactions) {

        $totalPendapatan = 0;
        $totalTransaksi = $transactions->count();

        $statusCount = [

            'baru' => 0,
            'proses' => 0,
            'selesai' => 0,
            'diambil' => 0

        ];

        $paymentCount = [

            'dibayar' => 0,
            'belum_dibayar' => 0

        ];

        foreach ($transactions as $transaction) {

            $totalTransaksiAmount = $this->calculateTransactionTotal($transaction);

            if ($transaction->dibayar === 'dibayar') {

                $totalPendapatan += $totalTransaksiAmount;

            }


            if (isset($statusCount[$transaction->status])) {

                $statusCount[$transaction->status]++;

            }


            if (isset($paymentCount[$transaction->dibayar])) {

                $paymentCount[$transaction->dibayar]++;

            }

        }

        return [

            'total_pendapatan' => $totalPendapatan,
            'total_transaksi' => $totalTransaksi,
            'status_count' => $statusCount,
            'payment_count' => $paymentCount

        ];

    }

    private function calculateTransactionTotal($transaction)  {

        try {

            $subtotal = 0;


            if ($transaction->detailTransaksi && $transaction->detailTransaksi->isNotEmpty()) {

                $subtotal = $transaction->detailTransaksi->sum(function ($detail) {

                    if ($detail->paket) {

                        return $detail->qty * $detail->paket->harga;

                    }

                    return $detail->qty * 0;

                });

            }

            $biaya_tambahan = $transaction->biaya_tambahan ?? 0;
            $diskon = $transaction->diskon ?? 0;
            $pajak = $transaction->pajak ?? 0;

            return $subtotal + $biaya_tambahan - $diskon + $pajak;

        } catch (\Exception $e) {

            \Log::warning('Error calculating total for transaction ' . ($transaction->id ?? 'unknown') . ': ' . $e->getMessage());
            return 0;

        }

    }

    private function exportReport($format, $transactions, $title, $statistics, $filters) {

        \Log::info('Exporting report', ['format' => $format, 'transaction_count' => $transactions->count()]);

        switch ($format) {

            case 'pdf':
                return $this->exportPDF($transactions, $title, $statistics, $filters);

            case 'excel':
                return $this->exportExcel($transactions, $title, $statistics, $filters);

            case 'view':
            default:
                return view('reports.result', compact('transactions', 'title', 'statistics', 'filters'));

        }

    }

    private function exportPDF($transactions, $title, $statistics, $filters) {

        try {

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf', compact('transactions', 'title', 'statistics', 'filters'));
            return $pdf->download('laporan-transaksi-' . date('d-m-Y') . '.pdf');

        } catch (\Exception $e) {

            \Log::error('PDF Generation Error: ' . $e->getMessage());
            return redirect()->route('reports.index')
                ->with('error', 'Gagal generate PDF: ' . $e->getMessage());

        }

    }

    private function exportExcel($transactions, $title, $statistics, $filters) {

        try {

            $filename = 'laporan-transaksi-' . date('Y-m-d-H-i-s') . '.xlsx';

            $headers = [

                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',

            ];

            $callback = function() use ($transactions, $title, $statistics, $filters) {

                $file = fopen('php://output', 'w');


                fwrite($file, "\xEF\xBB\xBF");


                fputcsv($file, [$title]);
                fputcsv($file, ['Dibuat pada: ' . date('d/m/Y H:i')]);
                fputcsv($file, ['Total Data: ' . $transactions->count() . ' transaksi']);
                fputcsv($file, []);


                fputcsv($file, [

                    'No', 'Kode Invoice', 'Outlet', 'Member', 'Tanggal Transaksi',
                    'Batas Waktu', 'Status', 'Status Pembayaran', 'Total (Rp)'

                ]);


                foreach ($transactions as $index => $transaction) {

                    $kodeInvoice = $transaction->kode_invoice ?? 'Tidak Ada Invoice';
                    $outletName = $transaction->outlet ? $transaction->outlet->nama : 'Outlet Tidak Ditemukan';
                    $memberName = $transaction->member ? $transaction->member->nama : 'Member Tidak Ditemukan';
                    $tanggal = $transaction->tgl ? $transaction->tgl->format('d/m/Y H:i') : '-';
                    $batasWaktu = $transaction->batas_waktu ? $transaction->batas_waktu->format('d/m/Y H:i') : '-';
                    $status = $this->getStatusText($transaction->status);
                    $pembayaran = $this->getPaymentStatusText($transaction->dibayar);
                    $total = $this->calculateTransactionTotal($transaction);

                    fputcsv($file, [

                        $index + 1,
                        $kodeInvoice,
                        $outletName,
                        $memberName,
                        $tanggal,
                        $batasWaktu,
                        $status,
                        $pembayaran,
                        number_format($total, 0, ',', '.')

                    ]);

                }


                fputcsv($file, []);
                fputcsv($file, []);


                fputcsv($file, ['RINGKASAN LAPORAN']);
                fputcsv($file, ['Total Transaksi:', $statistics['total_transaksi']]);
                fputcsv($file, ['Total Pendapatan:', 'Rp ' . number_format($statistics['total_pendapatan'], 0, ',', '.')]);
                fputcsv($file, ['Sudah Dibayar:', $statistics['payment_count']['dibayar']]);
                fputcsv($file, ['Belum Dibayar:', $statistics['payment_count']['belum_dibayar']]);
                fputcsv($file, []);
                fputcsv($file, ['Status Baru:', $statistics['status_count']['baru']]);
                fputcsv($file, ['Status Proses:', $statistics['status_count']['proses']]);
                fputcsv($file, ['Status Selesai:', $statistics['status_count']['selesai']]);
                fputcsv($file, ['Status Diambil:', $statistics['status_count']['diambil']]);

                fclose($file);

            };

            \Log::info('Excel file generated successfully', ['filename' => $filename]);
            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {

            \Log::error('Excel Generation Error: ' . $e->getMessage());
            return redirect()->route('reports.index')
                ->with('error', 'Gagal generate Excel: ' . $e->getMessage());

        }

    }

    private function getStatusText($status) {

        $statuses = [

            'baru' => 'Baru',
            'proses' => 'Proses',
            'selesai' => 'Selesai',
            'diambil' => 'Diambil'

        ];

        return $statuses[$status] ?? $status;

    }

    private function getPaymentStatusText($dibayar) {

        return $dibayar === 'dibayar' ? 'Sudah Dibayar' : 'Belum Dibayar';

    }

    private function logActivity($user, $activity, $relatedData = null) {

        try {

            Tblog::create([

                'id_user' => $user->id,
                'aktivitas' => $activity,
                'tanggal' => now(),
                'data_terkait' => $relatedData ? json_encode($relatedData) : null

            ]);

        } catch (\Exception $e) {

            \Log::error('Failed to log activity: ' . $e->getMessage());

        }

    }

}
