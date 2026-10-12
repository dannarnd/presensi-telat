import os
import docx
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import parse_xml, OxmlElement
from docx.oxml.ns import nsdecls, qn

def set_cell_background(cell, fill_hex):
    tcPr = cell._element.get_or_add_tcPr()
    shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{fill_hex}"/>')
    tcPr.append(shd)

def set_cell_margins(cell, top=100, bottom=100, left=150, right=150):
    tcPr = cell._element.get_or_add_tcPr()
    tcMar = parse_xml(f'<w:tcMar {nsdecls("w")}><w:top w:w="{top}" w:type="dxa"/><w:bottom w:w="{bottom}" w:type="dxa"/><w:left w:w="{left}" w:type="dxa"/><w:right w:w="{right}" w:type="dxa"/></w:tcMar>')
    tcPr.append(tcMar)

def set_callout_border(cell, color_hex="1E40AF"):
    tcPr = cell._element.get_or_add_tcPr()
    borders = parse_xml(f'<w:tcBorders {nsdecls("w")}><w:top w:val="none"/><w:left w:val="single" w:sz="24" w:space="0" w:color="{color_hex}"/><w:bottom w:val="none"/><w:right w:val="none"/></w:tcBorders>')
    tcPr.append(borders)

def set_table_borders(table, color_hex="CBD5E1"):
    tblPr = table._element.xpath('w:tblPr')
    if tblPr:
        borders = parse_xml(f'<w:tblBorders {nsdecls("w")}><w:top w:val="single" w:sz="4" w:space="0" w:color="{color_hex}"/><w:bottom w:val="single" w:sz="4" w:space="0" w:color="{color_hex}"/><w:insideH w:val="single" w:sz="4" w:space="0" w:color="{color_hex}"/><w:left w:val="none"/><w:right w:val="none"/><w:insideV w:val="none"/></w:tblBorders>')
        tblPr[0].append(borders)

def create_document():
    doc = docx.Document()
    
    # Page Margins: 1 inch (72 pt)
    sections = doc.sections
    for section in sections:
        section.top_margin = Inches(0.9)
        section.bottom_margin = Inches(0.9)
        section.left_margin = Inches(0.9)
        section.right_margin = Inches(0.9)
        
    # Styles definition
    COLOR_PRIMARY = RGBColor(30, 58, 138)    # #1E3A8A Navy
    COLOR_SECONDARY = RGBColor(2, 132, 199)  # #0284C7 Sky Blue
    COLOR_DARK = RGBColor(30, 41, 59)        # #1E293B Slate Dark
    COLOR_MUTED = RGBColor(100, 116, 139)    # #64748B Slate Muted
    COLOR_GOLD = RGBColor(180, 83, 9)        # #B45309 Amber
    
    # Assets
    assets_dir = r'C:\laragon\www\presensi-telat\doc_assets'
    logo_path = os.path.join(assets_dir, 'logo_sekolah.png')
    bagan_path = os.path.join(assets_dir, 'bagan_alur_sistem.png')
    tiket_path = os.path.join(assets_dir, 'tiket_rombongan.png')
    pantau_path = os.path.join(assets_dir, 'portal_pantau_guru.png')
    login_path = os.path.join(assets_dir, 'login_presensi.png')

    # =========================================================================
    # 1. KOP SURAT / HEADER RESMI
    # =========================================================================
    kop_table = doc.add_table(rows=1, cols=2)
    kop_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    kop_table.autofit = False
    
    col_widths = [Inches(1.2), Inches(5.5)]
    for row in kop_table.rows:
        for idx, width in enumerate(col_widths):
            row.cells[idx].width = width
            
    # Cell 0: Logo
    cell_logo = kop_table.cell(0, 0)
    cell_logo.vertical_alignment = WD_ALIGN_VERTICAL.CENTER
    p_logo = cell_logo.paragraphs[0]
    p_logo.alignment = WD_ALIGN_PARAGRAPH.CENTER
    if os.path.exists(logo_path):
        p_logo.add_run().add_picture(logo_path, width=Inches(1.05))
        
    # Cell 1: Text Kop
    cell_text = kop_table.cell(0, 1)
    cell_text.vertical_alignment = WD_ALIGN_VERTICAL.CENTER
    p_kop = cell_text.paragraphs[0]
    p_kop.paragraph_format.space_before = Pt(0)
    p_kop.paragraph_format.space_after = Pt(2)
    p_kop.paragraph_format.line_spacing = 1.1
    
    r1 = p_kop.add_run("PEMERINTAH ACEH • DINAS PENDIDIKAN\n")
    r1.font.name = "Segoe UI"
    r1.font.size = Pt(10)
    r1.font.bold = True
    r1.font.color.rgb = COLOR_MUTED
    
    r2 = p_kop.add_run("SMK NEGERI 5 TELKOM BANDA ACEH\n")
    r2.font.name = "Segoe UI"
    r2.font.size = Pt(14)
    r2.font.bold = True
    r2.font.color.rgb = COLOR_PRIMARY
    
    r3 = p_kop.add_run("BIDANG KEAHLIAN TEKNOLOGI INFORMASI DAN KOMUNIKASI\n")
    r3.font.name = "Segoe UI"
    r3.font.size = Pt(9.5)
    r3.font.bold = True
    r3.font.color.rgb = COLOR_SECONDARY
    
    r4 = p_kop.add_run("Jl. Panglima Nyak Makam No. 1, Kota Banda Aceh • https://smkn5telkombna.web.id")
    r4.font.name = "Segoe UI"
    r4.font.size = Pt(8.5)
    r4.font.italic = True
    r4.font.color.rgb = COLOR_MUTED

    # Garis Pembatas Kop
    p_line = doc.add_paragraph()
    p_line.paragraph_format.space_before = Pt(4)
    p_line.paragraph_format.space_after = Pt(14)
    r_line = p_line.add_run("━" * 58)
    r_line.font.name = "Segoe UI"
    r_line.font.size = Pt(14)
    r_line.font.bold = True
    r_line.font.color.rgb = COLOR_PRIMARY

    # =========================================================================
    # 2. BADGE KATEGORI & JUDUL ARTIKEL
    # =========================================================================
    p_badge = doc.add_paragraph()
    p_badge.paragraph_format.space_before = Pt(0)
    p_badge.paragraph_format.space_after = Pt(6)
    r_badge = p_badge.add_run("● WARTA INOVASI SEKOLAH & BEST PRACTICE PENDIDIKAN VOKASI")
    r_badge.font.name = "Segoe UI"
    r_badge.font.size = Pt(9.5)
    r_badge.font.bold = True
    r_badge.font.color.rgb = COLOR_GOLD

    p_title = doc.add_paragraph()
    p_title.paragraph_format.space_before = Pt(0)
    p_title.paragraph_format.space_after = Pt(6)
    p_title.paragraph_format.line_spacing = 1.15
    r_title = p_title.add_run("INOVASI DIGITAL GURU SMKN 5 TELKOM BANDA ACEH: HADIRKAN APLIKASI PRESENSI PEMBINAAN & PENGONTROLAN SISWA TERLAMBAT SECARA REAL-TIME")
    r_title.font.name = "Segoe UI"
    r_title.font.size = Pt(17)
    r_title.font.bold = True
    r_title.font.color.rgb = COLOR_PRIMARY

    p_meta = doc.add_paragraph()
    p_meta.paragraph_format.space_before = Pt(0)
    p_meta.paragraph_format.space_after = Pt(14)
    r_meta = p_meta.add_run("Banda Aceh | Kategori: Transformasi Digital Society 5.0 | Kolaborasi: Guru SMKN 5 Telkom & Mahasiswa PPKPM UIN Ar-Raniry 2026")
    r_meta.font.name = "Segoe UI"
    r_meta.font.size = Pt(9)
    r_meta.font.italic = True
    r_meta.font.color.rgb = COLOR_MUTED

    # =========================================================================
    # 3. LEAD PARAGRAPH (BERITA UTAMA)
    # =========================================================================
    p_lead = doc.add_paragraph()
    p_lead.paragraph_format.space_before = Pt(0)
    p_lead.paragraph_format.space_after = Pt(8)
    p_lead.paragraph_format.line_spacing = 1.25
    r_lead_city = p_lead.add_run("Banda Aceh – ")
    r_lead_city.font.name = "Segoe UI"
    r_lead_city.font.size = Pt(11)
    r_lead_city.font.bold = True
    r_lead_city.font.color.rgb = COLOR_DARK
    
    r_lead_text = p_lead.add_run(
        "Dalam upaya memperkuat penegakan kedisiplinan peserta didik sekaligus mempercepat transformasi digital tata kelola sekolah, "
        "guru SMK Negeri 5 Telkom Banda Aceh berhasil merancang dan meluncurkan inovasi aplikasi terpadu pengontrolan siswa terlambat. "
        "Inovasi yang berbasis teknologi komputasi awan (cloud) ini menjadi terobosan nyata dalam memanfaatkan ekosistem teknologi informasi "
        "untuk menciptakan iklim sekolah yang tertib, akuntabel, dan transparan, sejalan dengan tuntutan budaya kerja profesional industri kejuruan."
    )
    r_lead_text.font.name = "Segoe UI"
    r_lead_text.font.size = Pt(11)
    r_lead_text.font.color.rgb = COLOR_DARK

    p_body1 = doc.add_paragraph()
    p_body1.paragraph_format.space_before = Pt(0)
    p_body1.paragraph_format.space_after = Pt(12)
    p_body1.paragraph_format.line_spacing = 1.25
    r_body1 = p_body1.add_run(
        "Aplikasi inovatif ini lahir melalui kolaborasi intensif antara Guru SMKN 5 Telkom Banda Aceh, Bapak Heri Sudanto, "
        "bersama mahasiswa Praktik Pengalaman Lapangan / Pengabdian (PPKPM) dari UIN Ar-Raniry Banda Aceh. "
        "Sistem ini dirancang khusus guna menjawab tantangan pencatatan piket di gerbang sekolah yang selama ini masih mengandalkan lembaran kertas, "
        "sehingga kini seluruh data keterlambatan dapat direkam dan disinkronkan secara langsung (real-time) hanya dalam hitungan detik."
    )
    r_body1.font.name = "Segoe UI"
    r_body1.font.size = Pt(11)
    r_body1.font.color.rgb = COLOR_DARK

    # =========================================================================
    # 4. KOTAK KUTIPAN KEPALA SEKOLAH (CALLOUT QUOTE)
    # =========================================================================
    quote_table = doc.add_table(rows=1, cols=1)
    quote_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    quote_table.autofit = False
    quote_cell = quote_table.cell(0, 0)
    quote_cell.width = Inches(6.7)
    set_cell_background(quote_cell, "F1F5F9")
    set_callout_border(quote_cell, "1E3A8A")
    set_cell_margins(quote_cell, top=140, bottom=140, left=200, right=180)
    
    p_quote = quote_cell.paragraphs[0]
    p_quote.paragraph_format.space_before = Pt(0)
    p_quote.paragraph_format.space_after = Pt(4)
    p_quote.paragraph_format.line_spacing = 1.2
    
    r_q_open = p_quote.add_run("“Aplikasi yang dirancang oleh guru kami, Bapak Heri Sudanto bersama Mahasiswa PPL UIN Ar-Raniry Banda Aceh ini merupakan bukti nyata bahwa SMKN 5 Telkom Banda Aceh senantiasa proaktif menghadirkan solusi teknologi mutakhir. Melalui digitalisasi terpadu ini, pendataan kehadiran tidak lagi membebani waktu belajar di kelas. Proses pencatatan di gerbang menjadi tertib, data tersaji akurat tanpa manipulasi, dan yang terpenting: pembinaan siswa dapat dilakukan secara humanis dan terukur.”\n")
    r_q_open.font.name = "Segoe UI"
    r_q_open.font.size = Pt(10.5)
    r_q_open.font.italic = True
    r_q_open.font.color.rgb = COLOR_DARK
    
    r_q_author = p_quote.add_run("— Bapak Anas, S.Ag., M.A (Kepala SMK Negeri 5 Telkom Banda Aceh)")
    r_q_author.font.name = "Segoe UI"
    r_q_author.font.size = Pt(10)
    r_q_author.font.bold = True
    r_q_author.font.color.rgb = COLOR_PRIMARY

    # Spacing
    p_space1 = doc.add_paragraph()
    p_space1.paragraph_format.space_before = Pt(6)
    p_space1.paragraph_format.space_after = Pt(0)

    # =========================================================================
    # 5. BAGAN ALUR KERJA SISTEM (INFOGRAFIS)
    # =========================================================================
    p_h_flow = doc.add_paragraph()
    p_h_flow.paragraph_format.space_before = Pt(8)
    p_h_flow.paragraph_format.space_after = Pt(4)
    r_h_flow = p_h_flow.add_run("Arsitektur & Alur Operasional Terintegrasi")
    r_h_flow.font.name = "Segoe UI"
    r_h_flow.font.size = Pt(13)
    r_h_flow.font.bold = True
    r_h_flow.font.color.rgb = COLOR_PRIMARY

    p_flow_desc = doc.add_paragraph()
    p_flow_desc.paragraph_format.space_before = Pt(0)
    p_flow_desc.paragraph_format.space_after = Pt(6)
    r_flow_desc = p_flow_desc.add_run(
        "Alur penanganan keterlambatan menghubungkan 5 aktor penting sekolah secara seamless tanpa jeda birokrasi kertas:"
    )
    r_flow_desc.font.name = "Segoe UI"
    r_flow_desc.font.size = Pt(10.5)
    r_flow_desc.font.color.rgb = COLOR_DARK

    if os.path.exists(bagan_path):
        p_img_flow = doc.add_paragraph()
        p_img_flow.paragraph_format.space_before = Pt(2)
        p_img_flow.paragraph_format.space_after = Pt(2)
        p_img_flow.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p_img_flow.add_run().add_picture(bagan_path, width=Inches(6.6))
        
        p_cap_flow = doc.add_paragraph()
        p_cap_flow.paragraph_format.space_before = Pt(2)
        p_cap_flow.paragraph_format.space_after = Pt(12)
        p_cap_flow.alignment = WD_ALIGN_PARAGRAPH.CENTER
        r_cap_flow = p_cap_flow.add_run("Gambar 1. Bagan Alur Integrasi 5 Tahap Presensi Keterlambatan SMKN 5 Telkom Banda Aceh")
        r_cap_flow.font.name = "Segoe UI"
        r_cap_flow.font.size = Pt(8.5)
        r_cap_flow.font.italic = True
        r_cap_flow.font.color.rgb = COLOR_MUTED

    # =========================================================================
    # 6. LATAR BELAKANG & URGENSI INOVASI
    # =========================================================================
    p_h_latar = doc.add_paragraph()
    p_h_latar.paragraph_format.space_before = Pt(8)
    p_h_latar.paragraph_format.space_after = Pt(4)
    r_h_latar = p_h_latar.add_run("Tingkatkan Kedisiplinan: Dari Kertas Piket ke Paperless System")
    r_h_latar.font.name = "Segoe UI"
    r_h_latar.font.size = Pt(13)
    r_h_latar.font.bold = True
    r_h_latar.font.color.rgb = COLOR_PRIMARY

    p_latar = doc.add_paragraph()
    p_latar.paragraph_format.space_before = Pt(0)
    p_latar.paragraph_format.space_after = Pt(8)
    p_latar.paragraph_format.line_spacing = 1.25
    r_latar = p_latar.add_run(
        "Kedisiplinan waktu merupakan pilar utama pembentukan karakter vokasi. Sebagai SMK Pusat Keunggulan yang berorientasi "
        "pada dunia usaha dan industri (DUDI), keterlambatan siswa tidak bisa dipandang sebelah mata. Sebelum adanya sistem ini, "
        "petugas piket mencatat keterlambatan secara manual di buku jurnal. Hal tersebut menimbulkan sejumlah kendala operasional: "
        "antrean panjang di pos satpam, risiko hilangnya lembaran surat izin masuk, serta guru mata pelajaran di ruang kelas yang tidak "
        "mengetahui status aktual keberadaan siswa (apakah sedang ditahan piket atau membolos)."
    )
    r_latar.font.name = "Segoe UI"
    r_latar.font.size = Pt(11)
    r_latar.font.color.rgb = COLOR_DARK

    # Tabel Perbandingan Manual vs Digital
    comp_table = doc.add_table(rows=5, cols=3)
    comp_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    comp_table.autofit = False
    set_table_borders(comp_table, "CBD5E1")
    
    t_widths = [Inches(1.8), Inches(2.4), Inches(2.5)]
    headers = ["Aspek Pengelolaan", "Metode Manual Tradisional", "Inovasi Digital SMKN 5 Telkom"]
    for idx, width in enumerate(t_widths):
        cell = comp_table.cell(0, idx)
        cell.width = width
        set_cell_background(cell, "1E3A8A")
        set_cell_margins(cell, top=80, bottom=80, left=100, right=100)
        p = cell.paragraphs[0]
        p.paragraph_format.space_before = Pt(0)
        p.paragraph_format.space_after = Pt(0)
        r = p.add_run(headers[idx])
        r.font.name = "Segoe UI"
        r.font.size = Pt(9.5)
        r.font.bold = True
        r.font.color.rgb = RGBColor(255, 255, 255)
        
    rows_data = [
        ("Waktu Pencatatan di Gerbang", "3 - 5 menit per anak, memicu antrean panjang", "Kurang dari 10 detik per siswa via pencarian cepat"),
        ("Surat Izin Masuk Kelas", "Lembaran kertas fisik, boros & rawan hilang", "Tiket Digital Rombongan (Class Pass) langsung di layar/ponsel"),
        ("Informasi ke Guru Mapel", "Guru di kelas pasif menunggu kabar dari piket", "Live Monitor Realtime (/pantau) diakses langsung dari kelas"),
        ("Penanganan Kasus oleh BK", "Rekap manual berkala di akhir bulan", "Otomatisasi akumulasi pelanggaran & pembinaan berbasis data")
    ]
    
    for r_idx, row_data in enumerate(rows_data, start=1):
        for c_idx, text in enumerate(row_data):
            cell = comp_table.cell(r_idx, c_idx)
            cell.width = t_widths[c_idx]
            bg_color = "F8FAFC" if r_idx % 2 == 1 else "FFFFFF"
            set_cell_background(cell, bg_color)
            set_cell_margins(cell, top=70, bottom=70, left=100, right=100)
            p = cell.paragraphs[0]
            p.paragraph_format.space_before = Pt(0)
            p.paragraph_format.space_after = Pt(0)
            r = p.add_run(text)
            r.font.name = "Segoe UI"
            r.font.size = Pt(9)
            if c_idx == 0:
                r.font.bold = True
                r.font.color.rgb = COLOR_DARK
            elif c_idx == 1:
                r.font.color.rgb = RGBColor(185, 28, 28) # Red-ish
            else:
                r.font.bold = True
                r.font.color.rgb = RGBColor(4, 120, 87) # Emerald Green

    p_cap_tbl = doc.add_paragraph()
    p_cap_tbl.paragraph_format.space_before = Pt(2)
    p_cap_tbl.paragraph_format.space_after = Pt(12)
    p_cap_tbl.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_cap_tbl = p_cap_tbl.add_run("Tabel 1. Komparasi Efisiensi Tata Kelola Presensi Siswa Terlambat")
    r_cap_tbl.font.name = "Segoe UI"
    r_cap_tbl.font.size = Pt(8.5)
    r_cap_tbl.font.italic = True
    r_cap_tbl.font.color.rgb = COLOR_MUTED

    # =========================================================================
    # 7. 4 FITUR UTAMA & SHOWCASE VISUAL
    # =========================================================================
    p_h_fitur = doc.add_paragraph()
    p_h_fitur.paragraph_format.space_before = Pt(8)
    p_h_fitur.paragraph_format.space_after = Pt(4)
    r_h_fitur = p_h_fitur.add_run("Showcase Fitur Unggulan Sistem Presensi Pembinaan")
    r_h_fitur.font.name = "Segoe UI"
    r_h_fitur.font.size = Pt(13)
    r_h_fitur.font.bold = True
    r_h_fitur.font.color.rgb = COLOR_PRIMARY

    # Fitur 1: Tiket Rombongan
    p_f1 = doc.add_paragraph()
    p_f1.paragraph_format.space_before = Pt(4)
    p_f1.paragraph_format.space_after = Pt(4)
    p_f1.paragraph_format.line_spacing = 1.2
    r_f1_num = p_f1.add_run("1. Digital Class Pass: Solusi Tiket Masuk Kelas Tanpa Kertas (Paperless)\n")
    r_f1_num.font.name = "Segoe UI"
    r_f1_num.font.size = Pt(11)
    r_f1_num.font.bold = True
    r_f1_num.font.color.rgb = COLOR_SECONDARY
    
    r_f1_body = p_f1.add_run(
        "Ketika sejumlah siswa datang terlambat secara bersamaan pada kelas yang sama, petugas piket tidak perlu menulis kertas surat izin satu per satu. "
        "Aplikasi menyediakan fitur 'Tiket Rombongan' yang merangkum nama-nama siswa, jam kedatangan, dan alasan keterlambatan dalam satu tiket digital. "
        "Tiket ini dapat langsung diunduh berupa gambar atau ditunjukkan dari layar perangkat siswa ke Guru Mata Pelajaran di kelas."
    )
    r_f1_body.font.name = "Segoe UI"
    r_f1_body.font.size = Pt(10.5)
    r_f1_body.font.color.rgb = COLOR_DARK

    # Tampilkan Gambar Tiket Rombongan berdampingan dengan penjelasan atau ditengah
    if os.path.exists(tiket_path):
        p_img_t = doc.add_paragraph()
        p_img_t.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p_img_t.paragraph_format.space_before = Pt(2)
        p_img_t.paragraph_format.space_after = Pt(2)
        p_img_t.add_run().add_picture(tiket_path, width=Inches(3.2))
        
        p_cap_t = doc.add_paragraph()
        p_cap_t.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p_cap_t.paragraph_format.space_before = Pt(2)
        p_cap_t.paragraph_format.space_after = Pt(10)
        r_cap_t = p_cap_t.add_run("Gambar 2. Tampilan Tiket Rombongan (Class Pass) Izin Masuk Kelas Digital")
        r_cap_t.font.name = "Segoe UI"
        r_cap_t.font.size = Pt(8.5)
        r_cap_t.font.italic = True
        r_cap_t.font.color.rgb = COLOR_MUTED

    # Fitur 2: Monitor Guru Mapel
    p_f2 = doc.add_paragraph()
    p_f2.paragraph_format.space_before = Pt(6)
    p_f2.paragraph_format.space_after = Pt(4)
    p_f2.paragraph_format.line_spacing = 1.2
    r_f2_num = p_f2.add_run("2. Portal Monitor Real-Time Guru Mapel (/pantau): Transparansi Tanpa Hambatan Login\n")
    r_f2_num.font.name = "Segoe UI"
    r_f2_num.font.size = Pt(11)
    r_f2_num.font.bold = True
    r_f2_num.font.color.rgb = COLOR_SECONDARY
    
    r_f2_body = p_f2.add_run(
        "Salah satu inovasi terbesar yang diterapkan adalah ketersediaan halaman khusus (/pantau) bagi guru yang sedang mengajar. "
        "Guru tidak perlu direpotkan dengan mengingat password atau login akun; cukup membuka tautan monitor, sistem akan secara otomatis melakukan "
        "auto-refresh pembaruan data setiap detik. Dilengkapi filter kelas dan pencarian siswa, guru mapel dapat memverifikasi keterlambatan siswa "
        "secara objektif dan melihat riwayat pembinaan secara rapi dan profesional."
    )
    r_f2_body.font.name = "Segoe UI"
    r_f2_body.font.size = Pt(10.5)
    r_f2_body.font.color.rgb = COLOR_DARK

    if os.path.exists(pantau_path):
        p_img_p = doc.add_paragraph()
        p_img_p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p_img_p.paragraph_format.space_before = Pt(2)
        p_img_p.paragraph_format.space_after = Pt(2)
        p_img_p.add_run().add_picture(pantau_path, width=Inches(5.6))
        
        p_cap_p = doc.add_paragraph()
        p_cap_p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p_cap_p.paragraph_format.space_before = Pt(2)
        p_cap_p.paragraph_format.space_after = Pt(10)
        r_cap_p = p_cap_p.add_run("Gambar 3. Antarmuka Live Monitor Siswa Terlambat Khusus Guru Mata Pelajaran")
        r_cap_p.font.name = "Segoe UI"
        r_cap_p.font.size = Pt(8.5)
        r_cap_p.font.italic = True
        r_cap_p.font.color.rgb = COLOR_MUTED

    # Fitur 3: Multi-Role & Pembinaan BK Terpadu
    p_f3 = doc.add_paragraph()
    p_f3.paragraph_format.space_before = Pt(6)
    p_f3.paragraph_format.space_after = Pt(4)
    p_f3.paragraph_format.line_spacing = 1.2
    r_f3_num = p_f3.add_run("3. Sistem Autentikasi Berjenjang & Integrasi Penanganan Guru Bimbingan Konseling (BK)\n")
    r_f3_num.font.name = "Segoe UI"
    r_f3_num.font.size = Pt(11)
    r_f3_num.font.bold = True
    r_f3_num.font.color.rgb = COLOR_SECONDARY
    
    r_f3_body = p_f3.add_run(
        "Sistem ini bukan semata-mata alat pencatat pelanggaran, melainkan instrumen edukasi karakter. "
        "Aplikasi menyediakan kontrol hak akses berjenjang (Admin, Guru Piket, Guru BK, dan Wali Kelas). "
        "Guru BK memiliki panel khusus untuk menindaklanjuti siswa yang mengalami keterlambatan berulang. "
        "Setelah dilakukan konseling dan pembinaan, guru BK dapat memperbarui status penanganan serta mereset catatan aktif secara terpadu, "
        "sehingga tercipta proses bimbingan yang transparan dan memberi kesempatan siswa memperbaiki diri."
    )
    r_f3_body.font.name = "Segoe UI"
    r_f3_body.font.size = Pt(10.5)
    r_f3_body.font.color.rgb = COLOR_DARK

    if os.path.exists(login_path):
        p_img_l = doc.add_paragraph()
        p_img_l.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p_img_l.paragraph_format.space_before = Pt(2)
        p_img_l.paragraph_format.space_after = Pt(2)
        p_img_l.add_run().add_picture(login_path, width=Inches(5.0))
        
        p_cap_l = doc.add_paragraph()
        p_cap_l.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p_cap_l.paragraph_format.space_before = Pt(2)
        p_cap_l.paragraph_format.space_after = Pt(12)
        r_cap_l = p_cap_l.add_run("Gambar 4. Antarmuka Gerbang Masuk Sistem Presensi Pembinaan SMKN 5 Telkom Banda Aceh")
        r_cap_l.font.name = "Segoe UI"
        r_cap_l.font.size = Pt(8.5)
        r_cap_l.font.italic = True
        r_cap_l.font.color.rgb = COLOR_MUTED

    # =========================================================================
    # 8. PERNYATAAN GURU INOVATOR & MAHASISWA PPL
    # =========================================================================
    quote_table2 = doc.add_table(rows=1, cols=1)
    quote_table2.alignment = WD_TABLE_ALIGNMENT.CENTER
    quote_table2.autofit = False
    quote_cell2 = quote_table2.cell(0, 0)
    quote_cell2.width = Inches(6.7)
    set_cell_background(quote_cell2, "F8FAFC")
    set_callout_border(quote_cell2, "0284C7")
    set_cell_margins(quote_cell2, top=130, bottom=130, left=200, right=180)
    
    p_quote2 = quote_cell2.paragraphs[0]
    p_quote2.paragraph_format.space_before = Pt(0)
    p_quote2.paragraph_format.space_after = Pt(4)
    p_quote2.paragraph_format.line_spacing = 1.2
    
    r_q2_open = p_quote2.add_run("“Kami merancang aplikasi ini dari sudut pandang kebutuhan nyata bapak/ibu guru di lapangan. Guru piket membutuhkan pencatatan yang secepat kilat agar gerbang tidak macet, guru di kelas membutuhkan kepastian status siswa tanpa terganggu KBM-nya, dan guru BK membutuhkan rekam jejak yang valid. Kolaborasi bersama adik-adik mahasiswa PPL UIN Ar-Raniry Banda Aceh membuktikan bahwa sinergi akademisi kampus dan praktisi sekolah kejuruan dapat melahirkan produk digital siap pakai yang berdampak nyata.”\n")
    r_q2_open.font.name = "Segoe UI"
    r_q2_open.font.size = Pt(10.5)
    r_q2_open.font.italic = True
    r_q2_open.font.color.rgb = COLOR_DARK
    
    r_q2_author = p_quote2.add_run("— Bapak Heri Sudanto (Guru Inovator SMKN 5 Telkom Banda Aceh)")
    r_q2_author.font.name = "Segoe UI"
    r_q2_author.font.size = Pt(10)
    r_q2_author.font.bold = True
    r_q2_author.font.color.rgb = COLOR_SECONDARY

    # =========================================================================
    # 9. WUJUD TRANSFORMASI DIGITAL & SOCIETY 5.0
    # =========================================================================
    p_h_trans = doc.add_paragraph()
    p_h_trans.paragraph_format.space_before = Pt(12)
    p_h_trans.paragraph_format.space_after = Pt(4)
    r_h_trans = p_h_trans.add_run("Wujud Nyata Transformasi Digital Sekolah Era Society 5.0")
    r_h_trans.font.name = "Segoe UI"
    r_h_trans.font.size = Pt(13)
    r_h_trans.font.bold = True
    r_h_trans.font.color.rgb = COLOR_PRIMARY

    p_trans = doc.add_paragraph()
    p_trans.paragraph_format.space_before = Pt(0)
    p_trans.paragraph_format.space_after = Pt(8)
    p_trans.paragraph_format.line_spacing = 1.25
    r_trans = p_trans.add_run(
        "Pengembangan aplikasi ini menegaskan posisi guru SMKN 5 Telkom Banda Aceh yang tidak hanya bertindak sebagai pengajar di ruang kelas, "
        "melainkan juga sebagai inovator dan problem solver terhadap permasalahan nyata di satuan pendidikan. "
        "Inovasi ini selaras dengan paradigma Society 5.0, di mana teknologi informasi dan otomatisasi data dimanfaatkan untuk melayani kemanusiaan, "
        "meningkatkan martabat profesi guru, serta membentuk ekosistem sekolah yang adaptif terhadap percepatan teknologi masa kini."
    )
    r_trans.font.name = "Segoe UI"
    r_trans.font.size = Pt(11)
    r_trans.font.color.rgb = COLOR_DARK

    # =========================================================================
    # 10. SPESIFIKASI TEKNOLOGI & IDENTITAS SISTEM
    # =========================================================================
    p_h_spec = doc.add_paragraph()
    p_h_spec.paragraph_format.space_before = Pt(6)
    p_h_spec.paragraph_format.space_after = Pt(4)
    r_h_spec = p_h_spec.add_run("Spesifikasi Teknis & Lingkungan Operasional Sistem")
    r_h_spec.font.name = "Segoe UI"
    r_h_spec.font.size = Pt(13)
    r_h_spec.font.bold = True
    r_h_spec.font.color.rgb = COLOR_PRIMARY

    spec_table = doc.add_table(rows=6, cols=2)
    spec_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    spec_table.autofit = False
    set_table_borders(spec_table, "CBD5E1")
    
    spec_w = [Inches(2.2), Inches(4.5)]
    specs_data = [
        ("Nama Inovasi / Aplikasi", "Sistem Presensi Pembinaan Keterlambatan Siswa Berbasis Cloud"),
        ("Satuan Pendidikan Sasaran", "SMK Negeri 5 Telkom Banda Aceh"),
        ("Teknologi Framework & Basis Data", "Laravel 11, Inertia.js (Vue 3 Single Page Application), PostgreSQL Supabase Cloud"),
        ("Akses URL & Mobilitas", "https://www.smkn5telkombna.web.id (Dukungan Responsif Smartphone & Desktop)"),
        ("Pengguna Sistem (Multi-Role)", "Administrator, Guru Piket, Guru BK, Wali Kelas, dan Guru Mata Pelajaran"),
        ("Tim Pengembang & Kolaborasi", "Bapak Heri Sudanto (Guru SMKN 5 Telkom) & Tim PPL UIN Ar-Raniry Banda Aceh 2026")
    ]
    
    for r_idx, (k, v) in enumerate(specs_data):
        cell_k = spec_table.cell(r_idx, 0)
        cell_v = spec_table.cell(r_idx, 1)
        cell_k.width = spec_w[0]
        cell_v.width = spec_w[1]
        
        bg = "F8FAFC" if r_idx % 2 == 1 else "FFFFFF"
        set_cell_background(cell_k, bg)
        set_cell_background(cell_v, bg)
        set_cell_margins(cell_k, top=60, bottom=60, left=100, right=100)
        set_cell_margins(cell_v, top=60, bottom=60, left=100, right=100)
        
        pk = cell_k.paragraphs[0]
        pk.paragraph_format.space_before = Pt(0)
        pk.paragraph_format.space_after = Pt(0)
        rk = pk.add_run(k)
        rk.font.name = "Segoe UI"
        rk.font.size = Pt(9.5)
        rk.font.bold = True
        rk.font.color.rgb = COLOR_DARK
        
        pv = cell_v.paragraphs[0]
        pv.paragraph_format.space_before = Pt(0)
        pv.paragraph_format.space_after = Pt(0)
        rv = pv.add_run(v)
        rv.font.name = "Segoe UI"
        rv.font.size = Pt(9.5)
        rv.font.color.rgb = COLOR_DARK

    p_space_spec = doc.add_paragraph()
    p_space_spec.paragraph_format.space_before = Pt(4)
    p_space_spec.paragraph_format.space_after = Pt(0)

    # =========================================================================
    # 11. PENUTUP & LEMBAR TANDA TANGAN / PENGESAHAN
    # =========================================================================
    p_close = doc.add_paragraph()
    p_close.paragraph_format.space_before = Pt(6)
    p_close.paragraph_format.space_after = Pt(14)
    p_close.paragraph_format.line_spacing = 1.25
    r_close = p_close.add_run(
        "Keberhasilan pengembangan dan implementasi aplikasi presensi pembinaan ini menjadi bukti nyata bahwa inovasi besar berawal dari "
        "kepekaan terhadap kebutuhan sehari-hari di lingkungan sekolah. Dengan kreativitas guru, sinergi mahasiswa perguruan tinggi mitra, "
        "dan dukungan penuh pimpinan sekolah, SMKN 5 Telkom Banda Aceh terus melangkah mantap sebagai pelopor transformasi digital pendidikan kejuruan di Provinsi Aceh.\n\n"
        "“SMKN 5 Telkom Banda Aceh — Berinovasi dengan Teknologi, Membangun Disiplin, Mewujudkan Pendidikan Berkualitas.”"
    )
    r_close.font.name = "Segoe UI"
    r_close.font.size = Pt(11)
    r_close.font.bold = True
    r_close.font.color.rgb = COLOR_PRIMARY

    # Kolom Tanda Tangan
    sign_table = doc.add_table(rows=1, cols=2)
    sign_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    sign_table.autofit = False
    
    sign_w = [Inches(3.3), Inches(3.4)]
    
    # Kiri: Guru Pengembang
    cell_s1 = sign_table.cell(0, 0)
    cell_s1.width = sign_w[0]
    ps1 = cell_s1.paragraphs[0]
    ps1.paragraph_format.space_before = Pt(8)
    ps1.paragraph_format.space_after = Pt(0)
    ps1.paragraph_format.line_spacing = 1.15
    
    r_s1_1 = ps1.add_run("Mengetahui & Merancang,\nGuru Inovator SMKN 5 Telkom\n\n\n\n\n")
    r_s1_1.font.name = "Segoe UI"
    r_s1_1.font.size = Pt(10)
    r_s1_1.font.color.rgb = COLOR_DARK
    
    r_s1_2 = ps1.add_run("Heri Sudanto, S.Pd / Guru Pembina\n")
    r_s1_2.font.name = "Segoe UI"
    r_s1_2.font.size = Pt(10.5)
    r_s1_2.font.bold = True
    r_s1_2.font.underline = True
    r_s1_2.font.color.rgb = COLOR_DARK
    
    r_s1_3 = ps1.add_run("Tim Pengembang Aplikasi Presensi Digital")
    r_s1_3.font.name = "Segoe UI"
    r_s1_3.font.size = Pt(9)
    r_s1_3.font.color.rgb = COLOR_MUTED

    # Kanan: Kepala Sekolah
    cell_s2 = sign_table.cell(0, 1)
    cell_s2.width = sign_w[1]
    ps2 = cell_s2.paragraphs[0]
    ps2.paragraph_format.space_before = Pt(8)
    ps2.paragraph_format.space_after = Pt(0)
    ps2.paragraph_format.line_spacing = 1.15
    
    r_s2_1 = ps2.add_run("Banda Aceh, Oktober 2026\nKepala SMKN 5 Telkom Banda Aceh\n\n\n\n\n")
    r_s2_1.font.name = "Segoe UI"
    r_s2_1.font.size = Pt(10)
    r_s2_1.font.color.rgb = COLOR_DARK
    
    r_s2_2 = ps2.add_run("Anas, S.Ag., M.A\n")
    r_s2_2.font.name = "Segoe UI"
    r_s2_2.font.size = Pt(10.5)
    r_s2_2.font.bold = True
    r_s2_2.font.underline = True
    r_s2_2.font.color.rgb = COLOR_DARK
    
    r_s2_3 = ps2.add_run("NIP. Pembina / Kepala Sekolah")
    r_s2_3.font.name = "Segoe UI"
    r_s2_3.font.size = Pt(9)
    r_s2_3.font.color.rgb = COLOR_MUTED

    output_path = r'C:\laragon\www\presensi-telat\INOVASI DIGITAL GURU SMKN 5 TELKOM BANDA ACEH.docx'
    doc.save(output_path)
    print(f"Document successfully created and saved to: {output_path}")

if __name__ == "__main__":
    create_document()
