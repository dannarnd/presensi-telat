<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keterlambatan Siswa</title>
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

        .info {
            margin-bottom: 20px;
        }
        .info table {
            width: 100%;
        }
        .info td {
            padding: 3px 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e0;
            padding: 10px;
            text-align: left;
        }
        .data-table th {
            background-color: #f7fafc;
            font-weight: bold;
            color: #4a5568;
        }
        .text-center {
            text-align: center !important;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-green { background-color: #def7ec; color: #03543f; }
        .badge-yellow { background-color: #fef3c7; color: #92400e; }
        .badge-orange { background-color: #ffedd5; color: #9a3412; }
        .badge-red { background-color: #fde8e8; color: #9b1c1c; }
        
        .footer {
            margin-top: 50px;
            text-align: right;
            padding-right: 50px;
        }
        .closing-section {
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .signature-line {
            margin-top: 80px;
            display: inline-block;
            width: 200px;
            border-top: 1px solid #333;
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
        LAPORAN RIWAYAT KETERLAMBATAN SISWA
    </div>

    <div class="info">
        <table style="width: 50%;">
            <tr>
                <td width="30%"><strong>Filter Kelas</strong></td>
                <td width="5%">:</td>
                <td>{{ $filter_class }}</td>
            </tr>
            <tr>
                <td><strong>Tanggal Cetak</strong></td>
                <td>:</td>
                <td>{{ $date }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="15%">NISN</th>
                <th width="35%">Nama Lengkap</th>
                <th width="15%" class="text-center">Kelas</th>
                <th width="15%" class="text-center">Total Telat</th>
                <th width="15%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $index => $student)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $student->nisn }}</td>
                    <td>{{ $student->name }}</td>
                    <td class="text-center">{{ $student->schoolClass->name ?? '-' }}</td>
                    <td class="text-center">
                        <div>{{ $student->active_delay_logs_count }} aktif</div>
                        <div style="font-size: 10px; color: #666;">Total: {{ $student->delay_logs_count }}</div>
                    </td>
                    <td class="text-center">
                        @if($student->active_delay_logs_count >= 5)
                            <span class="badge badge-red">Sangat Sering</span>
                        @elseif($student->active_delay_logs_count >= 3)
                            <span class="badge badge-orange">Sering</span>
                        @elseif($student->active_delay_logs_count > 0)
                            <span class="badge badge-yellow">Pernah Telat</span>
                        @else
                            <span class="badge badge-green">Disiplin</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data siswa ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="closing-section">
        <div style="margin-top: 30px; text-align: justify; line-height: 1.5; font-size: 14px; text-indent: 40px; margin-bottom: 30px;">
            Demikian laporan riwayat keterlambatan siswa ini disusun dengan sebenar-benarnya berdasarkan data yang tercatat di dalam sistem presensi sekolah. Laporan ini dapat digunakan sebagai lampiran administrasi, bahan pembinaan, maupun pemanggilan orang tua/wali murid yang bersangkutan.
        </div>

        <div class="footer">
            <p>Banda Aceh, {{ $date }}</p>
            <p style="margin-bottom: 70px;">Mengetahui,</p>
            <p>_______________________</p>
            <p>Kepala Sekolah / Guru Piket</p>
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
