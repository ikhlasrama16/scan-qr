<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Request;


class ExportController extends Controller
{
    public function index()
    {
        return view('export');
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
        $now = Carbon::now();

        switch ($range) {
            case 'today':
                $from = $now->copy()->startOfDay();
                $to = $now->copy()->endOfDay();
                $filename = 'log_qr_' . $now->format('Ymd') . '_today.xlsx';
                break;

            case 'week':
                $from = $now->copy()->startOfWeek();
                $to = $now->copy()->endOfWeek();
                $filename = 'log_qr_week_' . $from->format('Ymd') . '_to_' . $to->format('Ymd') . '.xlsx';
                break;

            case 'month':
                $from = $now->copy()->startOfMonth();
                $to = $now->copy()->endOfMonth();
                $filename = 'log_qr_month_' . $from->format('Ym') . '.xlsx';
                break;

            default:
                return back()->with('error', 'Rentang waktu tidak valid.');
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


    // public function export()
    // {
    //     // 1. Ambil data dari database (contoh: dari tabel log_qr)
    //     $data = DB::table('log_qr')->get();

    //     // 2. Buat spreadsheet
    //     $spreadsheet = new Spreadsheet();
    //     $sheet = $spreadsheet->getActiveSheet();

    //     // 3. Header kolom
    //     $sheet->setCellValue('A1', 'Nama');
    //     $sheet->setCellValue('B1', 'Kantor');
    //     $sheet->setCellValue('C1', 'Lokasi Scan');
    //     $sheet->setCellValue('D1', 'Keterangan');
    //     $sheet->setCellValue('E1', 'Waktu');

    //     // 4. Tulis data
    //     $row = 2;
    //     foreach ($data as $log) {
    //         $sheet->setCellValue("A{$row}", $log->nama ?? '-');
    //         $sheet->setCellValue("B{$row}", $log->kantor ?? '-');
    //         $sheet->setCellValue("C{$row}", $log->lokasi_scan);
    //         $sheet->setCellValue("D{$row}", $log->keterangan);
    //         $sheet->setCellValue("E{$row}", $log->created_at);
    //         $row++;
    //     }

    //     // 5. Buat writer dan response
    //     $writer = new Xlsx($spreadsheet);
    //     $filename = 'log_qr_export_' . date('Ymd_His') . '.xlsx';

    //     // 6. Output file Excel ke browser
    //     return response()->streamDownload(function () use ($writer) {
    //         $writer->save('php://output');
    //     }, $filename, [
    //         'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    //     ]);
    // }
}
