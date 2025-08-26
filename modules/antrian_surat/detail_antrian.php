<?php
// mencegah direct access file PHP
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('location: ../../404.html');
    exit;
}

// Periksa session
if (!isset($_SESSION)) {
    session_start();
}

// Tampilkan error untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Muat autoloader Composer jika ada
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
}

// Cek hak akses
if (!isset($_SESSION['hak_akses']) || !in_array($_SESSION['hak_akses'], ['SuperAdmin', 'Pimpinan', 'SekretarisPimpinan'])) {
    echo "<script>alert('Akses ditolak!');</script>";
    header('location: ?module=login');
    exit;
}

/**
 * Menggabungkan gambar tanda tangan dengan dokumen PDF menggunakan mPDF.
 * (Fungsi ini telah diperbaiki untuk mengatasi error TypeError final)
 */
/**
 * Menggabungkan gambar tanda tangan dengan dokumen PDF menggunakan mPDF.
 * (Fungsi ini telah diperbaiki dengan pemanggilan AddPage() yang akurat)
 */
function mergeSignatureWithPDF($original_pdf, $signature_image, $signature_area, $canvas_width, $canvas_height, $output_path) {
    // Validasi input area tanda tangan
    if (!isset($signature_area['pageNumber']) || (int)$signature_area['pageNumber'] <= 0) {
        return ['success' => false, 'message' => 'Error: Nomor halaman untuk tanda tangan tidak valid.'];
    }
    $target_page_number = (int)$signature_area['pageNumber'];

    if (!class_exists(\Mpdf\Mpdf::class)) {
        return ['success' => false, 'message' => 'Error: Library mPDF tidak ditemukan. Jalankan "composer update".'];
    }
    
    // Inisialisasi mPDF
    $pdf = new \Mpdf\Mpdf();

    // Validasi file input
    if (!file_exists($original_pdf) || !is_readable($original_pdf)) {
        return ['success' => false, 'message' => 'File PDF asli tidak ditemukan atau tidak bisa dibaca. Path: ' . $original_pdf];
    }
    if (!file_exists($signature_image)) {
        return ['success' => false, 'message' => 'File gambar tanda tangan tidak ditemukan. Path: ' . $signature_image];
    }

    try {
        $page_count = $pdf->setSourceFile($original_pdf);
        if ($page_count === 0) {
            return ['success' => false, 'message' => 'File PDF tidak valid atau kosong.'];
        }
        if ($target_page_number > $page_count) {
            return ['success' => false, 'message' => 'Error: Halaman target ('.$target_page_number.') melebihi jumlah halaman dokumen ('.$page_count.').'];
        }

        for ($page = 1; $page <= $page_count; $page++) {
            $template_id = $pdf->importPage($page);
            
            // Tambahkan halaman kosong standar
            $pdf->AddPage();

            // Gunakan template, dan biarkan mPDF menyesuaikan ukuran halaman secara otomatis
            $pdf->useTemplate($template_id, 0, 0, null, null, true);

            // Letakkan tanda tangan HANYA jika ini adalah halaman yang ditargetkan
            if ($page === $target_page_number) {
                // Gunakan properti publik $pdf->w dan $pdf->h untuk mendapatkan ukuran halaman
                $pdfWidth = $pdf->w;
                $pdfHeight = $pdf->h;

                $scaleX = $pdfWidth / $canvas_width;
                $scaleY = $pdfHeight / $canvas_height;

                $x = min($signature_area['startX'], $signature_area['endX']) * $scaleX;
                $y = min($signature_area['startY'], $signature_area['endY']) * $scaleY;
                $width = abs($signature_area['endX'] - $signature_area['startX']) * $scaleX;
                $height = abs($signature_area['endY'] - $signature_area['startY']) * $scaleY;

                // Batasi posisi dan ukuran agar tidak keluar halaman
                $x = max(0, $x);
                $y = max(0, $y);
                if ($x + $width > $pdfWidth) $width = $pdfWidth - $x;
                if ($y + $height > $pdfHeight) $height = $pdfHeight - $y;

                // ==================================================================
                // === SOLUSI: Nonaktifkan auto page break sebelum menaruh gambar ===
                $pdf->SetAutoPageBreak(false, 0);

                // Tempatkan gambar tanda tangan pada koordinat yang tepat
                $pdf->Image($signature_image, $x, $y, $width, $height, 'PNG');
                
                // Aktifkan kembali auto page break ke mode default (praktik terbaik)
                $pdf->SetAutoPageBreak(true);
                // ==================================================================
            }
        }

        $output_dir = dirname($output_path);
        if (!is_dir($output_dir) && !mkdir($output_dir, 0777, true)) {
            return ['success' => false, 'message' => 'Gagal membuat direktori output: ' . $output_dir];
        }

        $pdf->Output($output_path, 'F');
        
        if (!file_exists($output_path)) {
            return ['success' => false, 'message' => 'File output tidak berhasil dibuat.'];
        }

        return ['success' => true, 'file_path' => $output_path];

    } catch (Exception $e) {
        error_log("PDF merge error: " . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
        return ['success' => false, 'message' => 'Error saat memproses PDF: ' . $e->getMessage()];
    }
}

// ... (Sisa kode Anda tidak perlu diubah, tetap sama seperti sebelumnya) ...
$id_pengajuan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pengajuan <= 0) {
    header('location: ?module=antrian_surat');
    exit;
}
if (!isset($mysqli)) { die("Koneksi database tidak tersedia."); }
$query = "SELECT p.*, j.nama_jenis, u.nama_user as nama_pengaju FROM tbl_pengajuan p 
          INNER JOIN tbl_jenis j ON p.jenis_dokumen = j.id_jenis 
          INNER JOIN tbl_user u ON p.id_pengaju = u.id_user 
          WHERE p.id_pengajuan = ? AND p.status_pengajuan = 'Menunggu'";
$stmt = mysqli_prepare($mysqli, $query);
mysqli_stmt_bind_param($stmt, "i", $id_pengajuan);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($result) === 0) {
    echo "<script>alert('Data pengajuan tidak ditemukan atau sudah diproses.'); window.location.href = '?module=antrian_surat';</script>";
    exit;
}
$data = mysqli_fetch_assoc($result);
$days_waiting = (strtotime(date('Y-m-d')) - strtotime($data['tanggal_pengajuan'])) / (60 * 60 * 24);
$id_pimpinan = $_SESSION['id_user'];
$query_ttd = "SELECT file_ttd, MAX(tanggal_ttd) as last_used 
              FROM tbl_pengajuan 
              WHERE id_penandatangan = ? AND file_ttd IS NOT NULL AND file_ttd != '' 
              GROUP BY file_ttd
              ORDER BY last_used DESC 
              LIMIT 6";
$stmt_ttd = mysqli_prepare($mysqli, $query_ttd);
mysqli_stmt_bind_param($stmt_ttd, "i", $id_pimpinan);
mysqli_stmt_execute($stmt_ttd);
$result_ttd = mysqli_stmt_get_result($stmt_ttd);
$riwayat_ttd = [];
while ($row_ttd = mysqli_fetch_assoc($result_ttd)) {
    if (file_exists($row_ttd['file_ttd'])) {
        $riwayat_ttd[] = $row_ttd['file_ttd'];
    }
}
// ... (Akhir dari kode PHP yang tidak diubah) ...


// Proses form
if ($_POST) {
    if (isset($_POST['action'])) {
        // ... (Kode untuk 'tolak' tidak berubah) ...
        if ($_POST['action'] == 'tolak') {
            $catatan_penolakan = mysqli_real_escape_string($mysqli, $_POST['catatan_penolakan']);
            $update_query = "UPDATE tbl_pengajuan SET status_pengajuan = 'Ditolak', catatan_pimpinan = ?, updated_at = NOW() WHERE id_pengajuan = ?";
            $stmt_update = mysqli_prepare($mysqli, $update_query);
            mysqli_stmt_bind_param($stmt_update, "si", $catatan_penolakan, $id_pengajuan);
            if (mysqli_stmt_execute($stmt_update)) {
                echo "<script>alert('Pengajuan berhasil ditolak.'); window.location.href = '?module=antrian_surat';</script>";
            } else {
                echo "<script>alert('Gagal menolak pengajuan.');</script>";
            }
            exit;
        } 
        
        elseif ($_POST['action'] == 'setujui') {
            $project_root = $_SERVER['DOCUMENT_ROOT'] . '/buib_itpln/';
            $ttd_source = $_POST['ttd_source'];
            $ttd_signature_data = $_POST['ttd_signature'];
            
            $signature_area_json = $_POST['signature_area'];
            $signature_area = json_decode($signature_area_json, true);
            
            $canvas_width = (float)$_POST['canvas_width'];
            $canvas_height = (float)$_POST['canvas_height'];
            
            $catatan = mysqli_real_escape_string($mysqli, $_POST['catatan_persetujuan']);
            
            $ttd_filename_relative = '';
            $ttd_filename_absolute = '';

            if ($ttd_source === 'saved') {
                $ttd_filename_relative = $ttd_signature_data;
                $ttd_filename_absolute = $project_root . $ttd_filename_relative;
                if (!file_exists($ttd_filename_absolute)) {
                    echo "<script>alert('Error: File tanda tangan dari riwayat tidak ditemukan.');</script>";
                    exit;
                }
            } else {
                $ttd_filename_relative = 'modules/dokumen/ttd/' . $id_pengajuan . '_' . time() . '.png';
                $ttd_filename_absolute = $project_root . $ttd_filename_relative;
                
                $ttd_data_clean = preg_replace('/^data:image\/\w+;base64,/', '', $ttd_signature_data);
                $ttd_data_clean = str_replace(' ', '+', $ttd_data_clean);
                
                if (!file_put_contents($ttd_filename_absolute, base64_decode($ttd_data_clean))) {
                    echo "<script>alert('Error saat menyimpan file tanda tangan.');</script>";
                    exit;
                }
            }
            
            if (strtolower(pathinfo($data['file_dokumen'], PATHINFO_EXTENSION)) !== 'pdf') {
                echo "<script>alert('Error: Hanya file PDF yang dapat ditandatangani.');</script>";
                exit;
            }

            $original_pdf_absolute = $project_root . $data['file_dokumen'];
            $signed_filename_relative = 'modules/dokumen/signed/' . 'signed_' . $id_pengajuan . '_' . time() . '.pdf';
            $signed_filename_absolute = $project_root . $signed_filename_relative;
            
            $mergeResult = mergeSignatureWithPDF($original_pdf_absolute, $ttd_filename_absolute, $signature_area, $canvas_width, $canvas_height, $signed_filename_absolute);
            
            // Hapus file ttd yang baru dibuat (bukan dari riwayat)
            if ($ttd_source !== 'saved' && file_exists($ttd_filename_absolute)) {
                unlink($ttd_filename_absolute);
            }

            if (!$mergeResult['success']) {
                echo "<script>alert('Gagal menandatangani dokumen: " . addslashes($mergeResult['message']) . "');</script>";
                exit;
            }

            $update_query = "UPDATE tbl_pengajuan SET 
                        status_pengajuan = 'Disetujui', 
                        catatan_pimpinan = ?,
                        id_penandatangan = ?,
                        tanggal_ttd = NOW(),
                        file_ttd = ?,
                        file_dokumen_signed = ?,
                        updated_at = NOW()
                        WHERE id_pengajuan = ?";
            
            $stmt_update = mysqli_prepare($mysqli, $update_query);
            mysqli_stmt_bind_param($stmt_update, "sissi", 
                $catatan, 
                $_SESSION['id_user'], 
                $ttd_filename_relative,
                $signed_filename_relative,
                $id_pengajuan
            );
            
            if (mysqli_stmt_execute($stmt_update)) {
                echo "<script>
                    alert('Pengajuan berhasil disetujui dan ditandatangani.');
                    window.location.href = '?module=antrian_surat';
                </script>";
            } else {
                echo "<script>alert('Error saat update database: " . mysqli_error($mysqli) . "');</script>";
            }
            exit;
        }
    }
}
?>

<div class="panel-header">
    <div class="page-inner py-4">
        <div class="d-flex align-items-left align-items-md-top flex-column flex-md-row">
            <div class="page-header">
                <h4 class="page-title"><i class="fas fa-signature mr-2"></i> Detail Antrian Tanda Tangan</h4>
                <ul class="breadcrumbs">
                    <li class="nav-home"><a href="?module=beranda"><i class="flaticon-home"></i></a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a href="?module=antrian_surat">Antrian Pengajuan</a></li>
                    <li class="separator"><i class="flaticon-right-arrow"></i></li>
                    <li class="nav-item"><a>Detail Pengajuan</a></li>
                </ul>
            </div>
            <div class="ml-md-auto py-2 py-md-0">
                <a href="?module=antrian_surat" class="btn btn-secondary btn-round">
                    <span class="btn-label"><i class="fa fa-arrow-left mr-2"></i></span> Kembali
                </a>
            </div>
        </div>
    </div>
</div>
<div class="page-inner mt--5">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-file-alt mr-2"></i>Detail Pengajuan</h5>
                        <div class="ml-auto">
                            <span class="badge <?php echo ($days_waiting >= 7) ? 'badge-danger' : (($days_waiting >= 3) ? 'badge-warning' : 'badge-success'); ?>">
                                <?php echo ($days_waiting >= 7) ? 'Urgent' : (($days_waiting >= 3) ? 'Tinggi' : 'Normal'); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr><td width="40%"><strong>Jenis:</strong></td><td><?php echo htmlspecialchars($data['nama_jenis']); ?></td></tr>
                                <tr><td><strong>Judul:</strong></td><td><?php echo htmlspecialchars($data['judul_surat']); ?></td></tr>
                                <tr><td><strong>No. Surat:</strong></td><td><?php echo htmlspecialchars($data['nomor_surat'] ?: '-'); ?></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr><td width="40%"><strong>Pengaju:</strong></td><td><?php echo htmlspecialchars($data['nama_pengaju']); ?></td></tr>
                                <tr><td><strong>Tgl. Masuk:</strong></td><td><?php echo date('d/m/Y H:i', strtotime($data['tanggal_pengajuan'])); ?></td></tr>
                                <tr><td><strong>Menunggu:</strong></td><td><?php echo ceil($days_waiting); ?> hari</td></tr>
                            </table>
                        </div>
                    </div>
                    <?php if (!empty($data['perihal'])): ?>
                        <div class="mt-3">
                            <strong>Perihal:</strong>
                            <div class="bg-light p-3 mt-2 rounded"><?php echo nl2br(htmlspecialchars($data['perihal'])); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-file-pdf mr-2"></i>Preview Dokumen</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['file_dokumen']) && file_exists($data['file_dokumen'])): ?>
                        <div id="pdf-container" class="pdf-viewer-area">
                            </div>
                    <?php else: ?>
                        <div class="alert alert-warning">Dokumen tidak tersedia.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-signature mr-2"></i>Proses Tanda Tangan</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills mb-3" id="signatureTab" role="tablist">
                        <li class="nav-item"><a class="nav-link active" id="draw-tab" data-toggle="tab" href="#draw" role="tab">Gambar</a></li>
                        <li class="nav-item"><a class="nav-link" id="upload-tab" data-toggle="tab" href="#upload" role="tab">Upload</a></li>
                        <li class="nav-item"><a class="nav-link" id="saved-tab" data-toggle="tab" href="#saved" role="tab">Riwayat</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="draw" role="tabpanel">
                            <canvas id="signatureCanvas" width="300" height="150" style="border: 2px solid #dee2e6; border-radius: 10px; background: white;"></canvas>
                            <button class="btn btn-secondary btn-block mt-2" id="clearCanvas"><i class="fas fa-eraser mr-2"></i>Bersihkan</button>
                        </div>
                        <div class="tab-pane fade" id="upload" role="tabpanel">
                            <input type="file" id="signatureFile" accept="image/*" style="display: none;">
                            <button class="btn btn-info btn-block" id="uploadImageBtn">📁 Pilih Gambar Tanda Tangan</button>
                            <div id="uploadPreview" class="mt-2" style="display: none; text-align: center;">
                                <img id="uploadedSignature" src="" alt="Uploaded Signature" style="max-width: 200px; max-height: 100px; border: 1px solid #ddd;">
                            </div>
                        </div>
                        <div class="tab-pane fade" id="saved" role="tabpanel">
                            <div class="row">
                                <?php foreach ($riwayat_ttd as $ttd): ?>
                                    <div class="col-6 mb-2 text-center">
                                        <img src="<?php echo htmlspecialchars($ttd); ?>" class="img-fluid saved-signature" data-path="<?php echo htmlspecialchars($ttd); ?>" style="max-height: 80px; border: 1px solid #ddd; cursor: pointer;">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <form id="approvalForm" method="POST">
                        <input type="hidden" name="action" value="setujui">
                        <input type="hidden" id="hiddenSignature" name="ttd_signature">
                        <input type="hidden" id="hiddenSignatureSource" name="ttd_source">
                        <input type="hidden" id="hiddenSignatureArea" name="signature_area">
                        <input type="hidden" id="hiddenCanvasWidth" name="canvas_width">
                        <input type="hidden" id="hiddenCanvasHeight" name="canvas_height">
                        <div class="form-group mt-3">
                            <label for="catatanPimpinan">Catatan Persetujuan (Opsional)</label>
                            <textarea class="form-control" id="catatanPimpinan" name="catatan_persetujuan" rows="3"></textarea>
                        </div>
                        <button type="button" class="btn btn-primary btn-block mt-3" id="selectAreaBtn"><i class="fas fa-arrows-alt mr-2"></i>Pilih Area Tanda Tangan</button>
                        <button type="button" class="btn btn-success btn-block mt-2" id="btnSetujui" disabled><i class="fas fa-check mr-2"></i>Setujui & Tandatangani</button>
                        <button type="button" class="btn btn-danger btn-block mt-2" data-toggle="modal" data-target="#rejectModal"><i class="fas fa-times mr-2"></i>Tolak</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Tolak Pengajuan</h5><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button></div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="catatanPenolakan">Catatan Penolakan</label>
                        <textarea class="form-control" id="catatanPenolakan" name="catatan_penolakan" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="action" value="tolak">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // === Inisialisasi Variabel Global ===
    let signatureData = null;
    let currentSignatureSource = 'draw';
    let pdfDoc = null;
    let selectedArea = null; // Akan menyimpan { pageNumber, startX, ... }
    let isSelecting = false;
    let isSelectionComplete = false;

    // === Referensi Elemen DOM ===
    const pdfContainer = document.getElementById('pdf-container');
    const signatureCanvas = document.getElementById('signatureCanvas');
    const signatureCtx = signatureCanvas.getContext('2d', { willReadFrequently: true });
    const selectAreaBtn = document.getElementById('selectAreaBtn');
    const btnSetujui = document.getElementById('btnSetujui');
    const uploadImageBtn = document.getElementById('uploadImageBtn');
    const signatureFileInput = document.getElementById('signatureFile');
    const pdfUrl = '<?php echo htmlspecialchars($data["file_dokumen"]); ?>';
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.worker.min.js';

    // === Fungsi Inti untuk PDF ===

    /**
     * Merender SEMUA halaman PDF ke dalam kontainer.
     * Dijalankan saat load pertama dan saat window resize.
     */
    async function renderAllPages() {
        if (!pdfDoc || !pdfContainer) return;

        pdfContainer.innerHTML = '<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-3">Memuat Dokumen...</p></div>';
        const containerWidth = pdfContainer.offsetWidth;
        
        // Hapus konten loading dan siapkan untuk canvas
        pdfContainer.innerHTML = '';

        for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
            const page = await pdfDoc.getPage(pageNum);
            const viewport = page.getViewport({ scale: 1 });
            const scale = (containerWidth - 40) / viewport.width; // 40px padding
            const scaledViewport = page.getViewport({ scale: scale });

            // Buat wrapper untuk setiap halaman (canvas + overlay)
            const pageWrapper = document.createElement('div');
            pageWrapper.className = 'pdf-page-wrapper';
            pageWrapper.dataset.pageNumber = pageNum;

            // Buat canvas
            const canvas = document.createElement('canvas');
            canvas.className = 'pdf-canvas';
            canvas.height = scaledViewport.height;
            canvas.width = scaledViewport.width;
            const ctx = canvas.getContext('2d');

            // Buat overlay
            const overlay = document.createElement('div');
            overlay.className = 'signature-overlay';

            // Render halaman
            await page.render({ canvasContext: ctx, viewport: scaledViewport }).promise;
            
            // Tambahkan elemen ke DOM
            pageWrapper.appendChild(canvas);
            pageWrapper.appendChild(overlay);
            pdfContainer.appendChild(pageWrapper);
        }
    }

    /**
     * Fungsi yang hanya berjalan sekali untuk memuat PDF.
     */
    async function initializePDFViewer() {
        try {
            const loadingTask = pdfjsLib.getDocument(pdfUrl);
            pdfDoc = await loadingTask.promise;
            renderAllPages();
        } catch (error) {
            console.error('Error loading PDF:', error);
            pdfContainer.innerHTML = `<div class="alert alert-danger">Gagal memuat PDF: ${error.message}</div>`;
        }
    }

    // === Logika Pemilihan Area Tanda Tangan ===

    function toggleSelectMode() {
        isSelecting = !isSelecting;
        document.querySelectorAll('.pdf-page-wrapper').forEach(wrapper => {
            wrapper.style.cursor = isSelecting ? 'crosshair' : 'default';
        });
        
        if (isSelecting) {
            selectAreaBtn.textContent = 'Batalkan Pilihan';
            selectAreaBtn.classList.replace('btn-primary', 'btn-warning');
            if (isSelectionComplete) {
                clearSelection();
            }
            alert('Pilih halaman dan area untuk tanda tangan dengan cara klik dan seret.');
        } else {
            selectAreaBtn.textContent = 'Pilih Area Tanda Tangan';
            selectAreaBtn.classList.replace('btn-warning', 'btn-primary');
        }
    }

    // Menerapkan event listener ke kontainer utama untuk menangani event dari elemen anak
    pdfContainer.addEventListener('mousedown', (e) => {
        if (!isSelecting || !e.target.classList.contains('pdf-canvas')) return;

        clearSelection(); // Hapus seleksi lama setiap kali memulai yang baru
        isSelectionComplete = false;
        
        const wrapper = e.target.closest('.pdf-page-wrapper');
        const pageNum = parseInt(wrapper.dataset.pageNumber, 10);
        const rect = e.target.getBoundingClientRect();
        
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        selectedArea = { pageNumber: pageNum, startX: x, startY: y, endX: x, endY: y };
        
        const overlay = wrapper.querySelector('.signature-overlay');
        overlay.style.display = 'block';
        updateOverlay(overlay, selectedArea);
    });

    pdfContainer.addEventListener('mousemove', (e) => {
        if (!isSelecting || !selectedArea || isSelectionComplete || !e.target.classList.contains('pdf-canvas')) return;
        
        const rect = e.target.getBoundingClientRect();
        selectedArea.endX = e.clientX - rect.left;
        selectedArea.endY = e.clientY - rect.top;

        const wrapper = e.target.closest('.pdf-page-wrapper');
        const overlay = wrapper.querySelector('.signature-overlay');
        updateOverlay(overlay, selectedArea);
    });

    pdfContainer.addEventListener('mouseup', (e) => {
        if (!isSelecting || !selectedArea || isSelectionComplete || !e.target.classList.contains('pdf-canvas')) return;
        
        const rect = e.target.getBoundingClientRect();
        selectedArea.endX = e.clientX - rect.left;
        selectedArea.endY = e.clientY - rect.top;

        const width = Math.abs(selectedArea.endX - selectedArea.startX);
        const height = Math.abs(selectedArea.endY - selectedArea.startY);

        if (width > 10 && height > 10) {
            isSelectionComplete = true;
            updateButtonState();
            toggleSelectMode(); // Otomatis keluar dari mode seleksi
            alert(`Area tanda tangan berhasil dipilih di halaman ${selectedArea.pageNumber}.`);
        } else {
            clearSelection();
            alert('Pilih area yang lebih besar.');
        }
    });
    
    function updateOverlay(overlay, area) {
        if (!overlay || !area) return;
        const width = Math.abs(area.endX - area.startX);
        const height = Math.abs(area.endY - area.startY);
        const left = Math.min(area.startX, area.endX);
        const top = Math.min(area.startY, area.endY);
        overlay.style.left = `${left}px`;
        overlay.style.top = `${top}px`;
        overlay.style.width = `${width}px`;
        overlay.style.height = `${height}px`;
    }

    function clearSelection() {
        document.querySelectorAll('.signature-overlay').forEach(o => o.style.display = 'none');
        selectedArea = null;
        isSelectionComplete = false;
        updateButtonState();
    }
    
    // === Logika Tanda Tangan (Gambar, Upload, Riwayat) - Tidak Banyak Berubah ===
    // ... (Kode untuk signature drawing, upload, saved, dan tab handler bisa dicopy dari file asli Anda) ...
    // ... Berikut adalah kodenya untuk kelengkapan ...
    let isSigDrawing = false;
    signatureCanvas.addEventListener('mousedown', (e) => { isSigDrawing = true; const rect = signatureCanvas.getBoundingClientRect(); signatureCtx.beginPath(); signatureCtx.moveTo(e.clientX - rect.left, e.clientY - rect.top); });
    signatureCanvas.addEventListener('mousemove', (e) => { if (!isSigDrawing) return; const rect = signatureCanvas.getBoundingClientRect(); signatureCtx.lineTo(e.clientX - rect.left, e.clientY - rect.top); signatureCtx.strokeStyle = '#000'; signatureCtx.lineWidth = 2; signatureCtx.lineCap = 'round'; signatureCtx.stroke(); });
    signatureCanvas.addEventListener('mouseup', () => { isSigDrawing = false; signatureData = signatureCanvas.toDataURL('image/png'); currentSignatureSource = 'draw'; updateButtonState(); });
    signatureCanvas.addEventListener('mouseout', () => { isSigDrawing = false; });
    document.getElementById('clearCanvas').addEventListener('click', () => { signatureCtx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height); signatureData = null; currentSignatureSource = 'draw'; updateButtonState(); });
    uploadImageBtn.addEventListener('click', () => signatureFileInput.click());
    signatureFileInput.addEventListener('change', (e) => { const file = e.target.files[0]; if (!file) return; const reader = new FileReader(); reader.onload = (event) => { document.getElementById('uploadedSignature').src = event.target.result; document.getElementById('uploadPreview').style.display = 'block'; signatureData = event.target.result; currentSignatureSource = 'upload'; updateButtonState(); }; reader.readAsDataURL(file); });
    document.querySelectorAll('.saved-signature').forEach(img => { img.addEventListener('click', () => { document.querySelectorAll('.saved-signature').forEach(other => other.classList.remove('border-primary', 'shadow')); img.classList.add('border-primary', 'shadow'); signatureData = img.src; currentSignatureSource = 'saved'; updateButtonState(); }); });
    document.querySelectorAll('a[data-toggle="tab"]').forEach(tab => { tab.addEventListener('shown.bs.tab', (event) => { currentSignatureSource = event.target.getAttribute('href').substring(1); document.getElementById('hiddenSignatureSource').value = currentSignatureSource; signatureData = null; signatureCtx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height); document.getElementById('uploadPreview').style.display = 'none'; signatureFileInput.value = ''; document.querySelectorAll('.saved-signature').forEach(img => img.classList.remove('border-primary', 'shadow')); updateButtonState(); }); });


    // === Event Listeners Tambahan & Logika Form ===
    function updateButtonState() {
        btnSetujui.disabled = !signatureData || !isSelectionComplete;
    }

    function debounce(func, delay) {
        let timeout;
        return function(...args) { clearTimeout(timeout); timeout = setTimeout(() => func.apply(this, args), delay); };
    }

    window.addEventListener('resize', debounce(renderAllPages, 250));
    selectAreaBtn.addEventListener('click', toggleSelectMode);

    btnSetujui.addEventListener('click', () => {
        if (btnSetujui.disabled) return;
        if (!confirm('Anda yakin ingin menyetujui dan menandatangani pengajuan ini?')) return;

        const canvas = pdfContainer.querySelector(`.pdf-page-wrapper[data-page-number='${selectedArea.pageNumber}'] canvas`);

        document.getElementById('hiddenSignature').value = signatureData;
        document.getElementById('hiddenSignatureSource').value = currentSignatureSource;
        document.getElementById('hiddenSignatureArea').value = JSON.stringify(selectedArea);
        document.getElementById('hiddenCanvasWidth').value = canvas.width;
        document.getElementById('hiddenCanvasHeight').value = canvas.height;
        
        btnSetujui.disabled = true;
        btnSetujui.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
        document.getElementById('approvalForm').submit();
    });

    // === Mulai Program ===
    initializePDFViewer();
});
</script>

<style>
/* *** PERUBAHAN STYLE: Disesuaikan untuk multi-halaman *** */
.pdf-viewer-area {
    max-width: 100%;
    overflow-y: auto; /* Aktifkan scroll vertikal jika perlu */
    max-height: 70vh; /* Batasi tinggi kontainer */
    border: 1px solid #ddd;
    border-radius: 5px;
    background: #f8f9fa;
    padding: 20px;
}
.pdf-page-wrapper {
    position: relative;
    margin: 0 auto 20px auto; /* Halaman di tengah, dengan jarak bawah */
    display: block;
    width: fit-content;
}
.pdf-canvas {
    display: block;
    border: 1px solid #ccc;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.signature-overlay {
    position: absolute;
    border: 2px dashed #e74c3c;
    background: rgba(231, 76, 60, 0.2);
    display: none;
    pointer-events: none;
}
#signatureCanvas { 
    touch-action: none; 
    display: block;
    margin: 0 auto;
}
.saved-signature { transition: all 0.2s ease; }
.saved-signature:hover { transform: scale(1.05); box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
.saved-signature.border-primary { border-width: 3px !important; box-shadow: 0 0 0 2px rgba(0,123,255,0.25); }
.btn:disabled { opacity: 0.65; cursor: not-allowed; }
</style>