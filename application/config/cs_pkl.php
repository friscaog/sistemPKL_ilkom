<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Durasi Cache
|--------------------------------------------------------------------------
|
| Durasi cache dalam menit.
|
*/
$config['pkl_cache_time'] = 60;

/*
|--------------------------------------------------------------------------
| Pengaturan Upload File Peserta
|--------------------------------------------------------------------------
*/
$config['pkl_transkrip_path'] = './uploads/transkrip/';
$config['pkl_transkrip_allowed_types'] = 'pdf';
$config['pkl_transkrip_max_size'] = 1024;

$config['pkl_surat_pernyataan_memenuhi_syarat_path'] = './uploads/surat_pernyataan_memenuhi_syarat/';
$config['pkl_surat_pernyataan_memenuhi_syarat_allowed_types'] = 'pdf';
$config['pkl_surat_pernyataan_memenuhi_syarat_max_size'] = 1024;

$config['pkl_surat_permohonan_path'] = './uploads/surat_permohonan/';
$config['pkl_surat_permohonan_allowed_types'] = 'pdf';
$config['pkl_surat_permohonan_max_size'] = 1024;

$config['pkl_surat_penerimaan_path'] = './uploads/surat_penerimaan/';
$config['pkl_surat_penerimaan_allowed_types'] = 'pdf';
$config['pkl_surat_penerimaan_max_size'] = 1024;

$config['pkl_laporan_draft_path'] = './uploads/laporan_draft/';
$config['pkl_laporan_draft_allowed_types'] = 'pdf';
$config['pkl_laporan_draft_max_size'] = 1024 * 5;

$config['pkl_surat_selesai_path'] = './uploads/surat_selesai/';
$config['pkl_surat_selesai_allowed_types'] = 'pdf';
$config['pkl_surat_selesai_max_size'] = 1024;

$config['pkl_laporan_revisi_path'] = './uploads/laporan_revisi/';
$config['pkl_laporan_revisi_allowed_types'] = 'pdf';
$config['pkl_laporan_revisi_max_size'] = 1024 * 5;

$config['pkl_laporan_lembar_pengesahan_path'] = './uploads/laporan_lembar_pengesahan/';
$config['pkl_laporan_lembar_pengesahan_allowed_types'] = 'pdf';
$config['pkl_laporan_lembar_pengesahan_max_size'] = 1024;

$config['pkl_bukti_pengumpulan_laporan_path'] = './uploads/bukti_pengumpulan_laporan/';
$config['pkl_bukti_pengumpulan_laporan_allowed_types'] = 'pdf';
$config['pkl_bukti_pengumpulan_laporan_max_size'] = 1024;

/*
|--------------------------------------------------------------------------
| Pengaturan Upload File Admin
|--------------------------------------------------------------------------
*/
$config['pkl_berkas_path'] = './uploads/berkas/';
$config['pkl_berkas_allowed_types'] = 'pdf|xls|xlsx|xl|ppt|pptx|rtf|doc|docx|word|zip|rar';

/*
|--------------------------------------------------------------------------
| Angka yang menunjukkan tahapan peserta.
|--------------------------------------------------------------------------
|
| Jika peserta telah berada pada suatu tahapan, berarti peserta telah
| melewati tahapan yang bernilai lebih rendah.
|
*/
$config['pkl_tahapan_num_pendaftaran'] = 0;
$config['pkl_tahapan_num_pelaksanaan'] = 1;
$config['pkl_tahapan_num_pasca_pkl'] = 2;
$config['pkl_tahapan_num_pasca_ujian'] = 3;
$config['pkl_tahapan_num_selesai'] = 4;

/*
|--------------------------------------------------------------------------
| Jenis status peserta dalam suatu tahapan.
|--------------------------------------------------------------------------
*/
$config['pkl_status_belum_lengkap'] = 'belum_lengkap';
$config['pkl_status_menunggu_konfirmasi_pembimbing'] = 'mengunggu_konfirmasi_pembimbing';
$config['pkl_status_pending'] = 'pending';
$config['pkl_status_approved'] = 'approved';

/*
|--------------------------------------------------------------------------
| Jumlah informasi yang muncul dalam satu halaman.
|--------------------------------------------------------------------------
*/
$config['pkl_informasi_num_per_page'] = 5;
$config['pkl_download_num_per_page'] = 5;