<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Analisa Keterlambatan</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .page-spacer { height: 20mm; }
        .content-wrapper { padding: 0 20mm; }
        .header {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        .header table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }
        .header .logo {
            width: 15%;
            text-align: left;
        }
        .header .logo img {
            width: 90px;
            height: auto;
        }
        .header .kop-text {
            width: 85%;
            text-align: center;
        }
        .header .kop-text h2 {
            margin: 0;
            font-size: 16px;
            font-weight: normal;
        }
        .header .kop-text h1 {
            margin: 5px 0;
            font-size: 22px;
            font-weight: bold;
            color: #000;
        }
        .header .kop-text p {
            margin: 0;
            font-size: 12px;
            color: #000;
        }
        
        .title-report {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
        }

        .summary-box {
            border: 1px solid #cbd5e0;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f7fafc;
        }
        
        .summary-box h3 {
            margin-top: 0;
            color: #2d3748;
            font-size: 16px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e0;
            padding: 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #edf2f7;
            font-weight: bold;
            color: #4a5568;
        }
        .text-center {
            text-align: center !important;
        }
        
        .footer {
            margin-top: 50px;
            text-align: right;
            padding-right: 50px;
        }
        .closing-section {
            page-break-inside: avoid;
            break-inside: avoid;
        }
    </style>
</head>
<body>
<table style="width: 100%; border: none;">
    <thead><tr><td><div class="page-spacer"></div></td></tr></thead>
    <tbody><tr><td class="content-wrapper">

    <div class="header">
        <table>
            <tr>
                <td class="logo">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo SMK">
                </td>
                <td class="kop-text">
                    <h2>PEMERINTAH ACEH</h2>
                    <h2>DINAS PENDIDIKAN</h2>
                    <h1>SMK NEGERI 5 TELKOM BANDA ACEH</h1>
                    <p>Jln. Stadion H. Dimurthala No. 5 Lampinueng Kel. Kota Baru No.2 Telp/ Fax. (0651) 7552314 Kode Pos. 23125</p>
                    <p>Email: smkn5telkombandaaceh@gmail.com | Website: smkn5telkombandaaceh.sch.id</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="title-report">
        LAPORAN ANALISA KETERLAMBATAN SISWA<br>
        <span style="font-size: 12px; font-weight: normal;">Dicetak pada: {{ $date }}</span>
    </div>

    <div class="summary-box">
        <h3>Ringkasan Keterlambatan ({{ $periodLabel }})</h3>
        <p>Total Pelanggaran: <strong>{{ $totalLate }} keterlambatan</strong></p>
        <p>Tingkat Penanganan BK: <strong>{{ $resolutionRate }}% ({{ $resolvedLogs }} selesai dari {{ $resolvedLogs + $activeLogs }} siswa wajib BK)</strong></p>
    </div>

    <h3>Monitoring Penanganan Keterlambatan Siswa ({{ $periodLabel }})</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th width="7%" class="text-center">No</th>
                <th width="18%">NISN</th>
                <th width="27%">Nama Siswa</th>
                <th width="13%" class="text-center">Kelas</th>
                <th width="10%" class="text-center">Total Telat</th>
                <th width="25%">Status Penanganan BK</th>
            </tr>
        </thead>
        <tbody>
            @forelse($wallOfShame as $index => $student)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $student->nisn }}</td>
                    <td><strong>{{ $student->name }}</strong></td>
                    <td class="text-center">{{ $student->class_name }}</td>
                    <td class="text-center"><strong>{{ $student->total_late }}x</strong></td>
                    <td>
                        @if($student->total_late >= 6)
                            @if($student->counseling_count > 0 && ($student->last_counseling_delay_count >= $student->total_late || str_contains($student->last_counseling_stage, 'Tahap 3') || str_contains($student->last_counseling_stage, 'SP')))
                                <span style="color: #059669; font-weight: bold;">Tuntas (SP / Konferensi)</span><br>
                                <small style="color: #6b7280;">Oleh: {{ $student->last_counselor_name ?? 'Guru BK' }}</small>
                            @else
                                <span style="color: #b91c1c; font-weight: bold;">Wajib SP / Konferensi Kasus</span>
                            @endif
                        @elseif($student->total_late >= 5)
                            @if($student->counseling_count > 0 && ($student->last_counseling_delay_count >= $student->total_late || str_contains($student->last_counseling_stage, 'Tahap 2') || str_contains($student->last_counseling_stage, 'Panggilan')))
                                <span style="color: #059669; font-weight: bold;">Tuntas (Panggilan Ortu)</span><br>
                                <small style="color: #6b7280;">Oleh: {{ $student->last_counselor_name ?? 'Guru BK' }}</small>
                            @else
                                <span style="color: #b91c1c; font-weight: bold;">Wajib Panggilan Ortu (Tahap 2)</span>
                            @endif
                        @elseif($student->total_late >= 3)
                            @if($student->counseling_count > 0)
                                <span style="color: #2563eb; font-weight: bold;">Tuntas (Bimbingan Siswa)</span><br>
                                <small style="color: #6b7280;">Oleh: {{ $student->last_counselor_name ?? 'Guru BK' }}</small>
                            @else
                                <span style="color: #d97706; font-weight: bold;">Wajib Bimbingan Siswa (Tahap 1)</span>
                            @endif
                        @else
                            <span style="color: #6b7280;">Pembinaan Wali Kelas (1-2x)</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data keterlambatan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="closing-section">
        <div style="margin-top: 30px; text-align: justify; line-height: 1.5; font-size: 14px; text-indent: 40px; margin-bottom: 30px;">
            Demikian laporan analisa keterlambatan siswa ini disusun dengan sebenar-benarnya berdasarkan data harian yang tercatat di dalam sistem presensi sekolah. Laporan ini diharapkan dapat digunakan sebagai bahan evaluasi, pembinaan, serta tindak lanjut yang diperlukan untuk meningkatkan kedisiplinan siswa di lingkungan SMK Negeri 5 Telkom Banda Aceh.
        </div>

        <div class="footer">
            <p>Banda Aceh, {{ $date }}</p>
            <p style="margin-bottom: 70px;">Mengetahui,</p>
            <p>_______________________</p>
            <p>Kepala Sekolah</p>
        </div>
    </div>

    </td></tr></tbody>
    <tfoot><tr><td><div class="page-spacer"></div></td></tr></tfoot>
</table>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
