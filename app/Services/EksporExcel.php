<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class EksporExcel
{
    /**
     * @param array<int,string> $kepala
     * @param array<int,array<int,mixed>> $baris
     */
    public static function buat(string $judul, array $kepala, array $baris): string
    {
        return self::buatMultiSheet([
            [
                'judul' => $judul,
                'kepala' => $kepala,
                'baris' => $baris,
            ],
        ]);
    }

    /**
     * Membuat file .xlsx dengan beberapa sheet terpisah dan header bertingkat (opsional) agar sama dengan pratinjau.
     *
     * @param array<int,array{
     *     judul:string,
     *     subjudul?:string,
     *     kepala:array<int,string>,
     *     grupHeader?:array<int,array{label:string,mulaiKolom:int,jumlahKolom:int}>,
     *     baris:array<int,array<int,mixed>>,
     *     rataKananKolom?:array<int,int>,
     *     rataTengahKolom?:array<int,int>
     * }> $daftarSheet
     */
    public static function buatMultiSheet(array $daftarSheet): string
    {
        $buku = new Spreadsheet();
        $buku->removeSheetByIndex(0);

        $tulisSel = function ($lembar, int $kolom, int $baris, mixed $nilai): void {
            $sel = Coordinate::stringFromColumnIndex($kolom) . $baris;
            if (is_int($nilai) || is_float($nilai)) {
                $lembar->setCellValueExplicit($sel, $nilai, DataType::TYPE_NUMERIC);
            } else {
                $lembar->setCellValueExplicit($sel, (string) ($nilai ?? ''), DataType::TYPE_STRING);
            }
        };

        $gayaHeader = [
            'font' => ['bold' => true, 'size' => 10.5, 'color' => ['rgb' => '111827']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EEF2F6'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '111827'],
                ],
            ],
        ];

        foreach ($daftarSheet as $idxSheet => $cfg) {
            $judulMentah = (string) ($cfg['judul'] ?? ('Sheet ' . ($idxSheet + 1)));
            $namaSheet = mb_substr(preg_replace('/[\[\]\*\?:\\\\\/]/', ' ', $judulMentah) ?: 'Data', 0, 31);

            $lembar = $buku->createSheet();
            $lembar->setTitle($namaSheet);

            $kepala = array_values($cfg['kepala'] ?? []);
            $grupHeader = $cfg['grupHeader'] ?? [];
            $barisData = array_values($cfg['baris'] ?? []);
            $jumlahKolom = max(1, count($kepala));
            $kolomAkhir = Coordinate::stringFromColumnIndex($jumlahKolom);

            $rataKanan = array_flip($cfg['rataKananKolom'] ?? []);
            $rataTengah = array_flip($cfg['rataTengahKolom'] ?? [1]);

            $barisAktif = 1;

            // Judul utama sheet
            $tulisSel($lembar, 1, $barisAktif, $judulMentah);
            if ($jumlahKolom > 1) {
                $lembar->mergeCells("A{$barisAktif}:{$kolomAkhir}{$barisAktif}");
            }
            $lembar->getStyle("A{$barisAktif}:{$kolomAkhir}{$barisAktif}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '111827']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $lembar->getRowDimension($barisAktif)->setRowHeight(24);
            $barisAktif++;

            // Subjudul opsional
            if (! empty($cfg['subjudul'])) {
                $subjudul = (string) $cfg['subjudul'];
                $tulisSel($lembar, 1, $barisAktif, $subjudul);
                if ($jumlahKolom > 1) {
                    $lembar->mergeCells("A{$barisAktif}:{$kolomAkhir}{$barisAktif}");
                }
                $lembar->getStyle("A{$barisAktif}:{$kolomAkhir}{$barisAktif}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '374151']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $lembar->getRowDimension($barisAktif)->setRowHeight(20);
                $barisAktif++;
            }

            $barisAktif++; // Baris kosong pemisah sebelum tabel

            if ($kepala !== []) {
                if ($grupHeader !== []) {
                    $barisAtas = $barisAktif;
                    $barisBawah = $barisAktif + 1;

                    // Petakan kolom mana saja yang masuk grup (colspan)
                    $kolomDalamGrup = [];
                    foreach ($grupHeader as $grup) {
                        $mulai = (int) $grup['mulaiKolom'];
                        $jml = max(1, (int) $grup['jumlahKolom']);
                        $akhir = min($jumlahKolom, $mulai + $jml - 1);

                        for ($k = $mulai; $k <= $akhir; $k++) {
                            $kolomDalamGrup[$k] = true;
                        }

                        $hurufMulai = Coordinate::stringFromColumnIndex($mulai);
                        $hurufAkhir = Coordinate::stringFromColumnIndex($akhir);
                        $tulisSel($lembar, $mulai, $barisAtas, (string) $grup['label']);
                        if ($akhir > $mulai) {
                            $lembar->mergeCells("{$hurufMulai}{$barisAtas}:{$hurufAkhir}{$barisAtas}");
                        }
                    }

                    // Kolom non-grup di-merge vertikal (rowspan 2)
                    for ($c = 1; $c <= $jumlahKolom; $c++) {
                        $huruf = Coordinate::stringFromColumnIndex($c);
                        if (! isset($kolomDalamGrup[$c])) {
                            $tulisSel($lembar, $c, $barisAtas, $kepala[$c - 1]);
                            $lembar->mergeCells("{$huruf}{$barisAtas}:{$huruf}{$barisBawah}");
                        } else {
                            $tulisSel($lembar, $c, $barisBawah, $kepala[$c - 1]);
                        }
                    }

                    $lembar->getStyle("A{$barisAtas}:{$kolomAkhir}{$barisBawah}")->applyFromArray($gayaHeader);
                    $lembar->getRowDimension($barisAtas)->setRowHeight(24);
                    $lembar->getRowDimension($barisBawah)->setRowHeight(30);
                    $barisAktif = $barisBawah + 1;
                } else {
                    foreach ($kepala as $c => $teksHeader) {
                        $tulisSel($lembar, $c + 1, $barisAktif, $teksHeader);
                    }
                    $lembar->getStyle("A{$barisAktif}:{$kolomAkhir}{$barisAktif}")->applyFromArray($gayaHeader);
                    $lembar->getRowDimension($barisAktif)->setRowHeight(30);
                    $barisAktif++;
                }
            }

            $barisAwalData = $barisAktif;
            foreach ($barisData as $isiBaris) {
                $kolomNilai = array_values($isiBaris);
                foreach ($kolomNilai as $c => $nilai) {
                    $noKolom = $c + 1;
                    $tulisSel($lembar, $noKolom, $barisAktif, $nilai);

                    $selCoord = Coordinate::stringFromColumnIndex($noKolom) . $barisAktif;
                    $align = Alignment::HORIZONTAL_LEFT;
                    if (isset($rataTengah[$noKolom])) {
                        $align = Alignment::HORIZONTAL_CENTER;
                    } elseif (isset($rataKanan[$noKolom])) {
                        $align = Alignment::HORIZONTAL_RIGHT;
                    }

                    $lembar->getStyle($selCoord)->getAlignment()
                        ->setHorizontal($align)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }

                $lembar->getStyle("A{$barisAktif}:{$kolomAkhir}{$barisAktif}")->applyFromArray([
                    'font' => ['size' => 10.5, 'color' => ['rgb' => '111827']],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                ]);
                $lembar->getRowDimension($barisAktif)->setRowHeight(21);
                $barisAktif++;
            }

            if ($kepala !== []) {
                for ($c = 1; $c <= $jumlahKolom; $c++) {
                    $dim = $lembar->getColumnDimensionByColumn($c);
                    $dim->setAutoSize(true);
                }
                $lembar->calculateColumnWidths();

                for ($c = 1; $c <= $jumlahKolom; $c++) {
                    $dim = $lembar->getColumnDimensionByColumn($c);
                    $lebarHitung = (float) $dim->getWidth();
                    $dim->setAutoSize(false);
                    $lebarMin = $c === 1 ? 7 : ($c === 2 ? 26 : 16);
                    $dim->setWidth(max($lebarMin, min(48, $lebarHitung + 4)));
                }
            }

            if ($barisAktif > $barisAwalData) {
                $barisAkhirData = $barisAktif - 1;
                $lembar->getStyle("A{$barisAwalData}:{$kolomAkhir}{$barisAkhirData}")
                    ->getBorders()
                    ->getOutline()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()
                    ->setRGB('111827');
            }
        }

        if ($buku->getSheetCount() > 0) {
            $buku->setActiveSheetIndex(0);
        }

        ob_start();
        (new Xlsx($buku))->save('php://output');

        return (string) ob_get_clean();
    }
}