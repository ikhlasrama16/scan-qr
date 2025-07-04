<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Request;


class ExportController extends Controller
{
    public function indexPreset()
    {
        // Ambil semua minggu unik
        $dates = DB::table('log_qr')
            ->select(DB::raw('YEAR(created_at) as year'), DB::raw('WEEK(created_at, 1) as week'), DB::raw('MIN(created_at) as sample_date'))
            ->groupBy('year', 'week')
            ->orderBy('year', 'desc')
            ->orderBy('week', 'desc')
            ->get()
            ->map(function ($item) {
                $date = Carbon::parse($item->sample_date);
                $monthName = $date->locale('id')->isoFormat('MMMM'); // Nama bulan dalam bahasa Indonesia
                $weekOfMonth = ceil($date->weekOfMonth); // Hitung minggu ke-x dalam bulan
                $item->label = "Minggu ke-{$weekOfMonth} {$monthName} {$item->year}";
                return $item;
            });

        // Ambil semua bulan unik
        $months = DB::table('log_qr')
            ->select(DB::raw('YEAR(created_at) as year'), DB::raw('MONTH(created_at) as month'))
            ->distinct()
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                $item->label = $date->locale('id')->isoFormat('MMMM YYYY');
                return $item;
            });

        return view('export', compact('dates', 'months'));
    }



    public function exportAll()
    {
        return $this->downloadExcel(DB::table('log_qr')->get(), 'log_qr_all.xlsx');
    }


    public function exportRange(Request $request)
    {
        // 1. Validasi parameter
        $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        // 2. Ubah string → Carbon & buat rentang 24 jam penuh
        $from = Carbon::parse($request->from)->startOfDay(); // 00:00:00
        $to   = Carbon::parse($request->to)->endOfDay();     // 23:59:59

        // 3. Query pakai whereBetween
        $data = DB::table('log_qr')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get();

        // 4. Nama file
        $filename = "log_qr_{$request->from}_to_{$request->to}.xlsx";

        // 5. Download Excel
        return $this->downloadExcel($data, $filename);
    }


    public function exportPreset(Request $request)
    {
        $range = $request->input('range');

        if (str_starts_with($range, 'week-')) {
            [$prefix, $year, $week] = explode('-', $range);
            $from = Carbon::now()->setISODate($year, $week)->startOfWeek();
            $to = Carbon::now()->setISODate($year, $week)->endOfWeek();
            $filename = "log_qr_week_{$year}_{$week}.xlsx";
        } elseif (str_starts_with($range, 'month-')) {
            [$prefix, $year, $month] = explode('-', $range);
            $from = Carbon::create($year, $month, 1)->startOfMonth();
            $to = Carbon::create($year, $month, 1)->endOfMonth();
            $filename = "log_qr_month_{$year}_{$month}.xlsx";
        } else {
            return back()->with('error', 'Rentang preset tidak valid.');
        }

        $data = DB::table('log_qr')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'asc')
            ->get();

        return $this->downloadExcel($data, $filename);
    }


    // Fungsi export ke Excel
    private function downloadExcel($data, $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', 'Nama');
        $sheet->setCellValue('B1', 'Kantor');
        $sheet->setCellValue('C1', 'Lokasi Scan');
        $sheet->setCellValue('D1', 'Keterangan');
        $sheet->setCellValue('E1', 'Waktu');

        $row = 2;
        foreach ($data as $log) {
            $sheet->setCellValue("A{$row}", $log->nama ?? '-');
            $sheet->setCellValue("B{$row}", $log->kantor ?? '-');
            $sheet->setCellValue("C{$row}", $log->lokasi_scan);
            $sheet->setCellValue("D{$row}", $log->keterangan);
            $sheet->setCellValue("E{$row}", $log->created_at);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
