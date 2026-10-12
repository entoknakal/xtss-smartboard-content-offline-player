# XTSS Smartboard Offline Player

## Version
- 1.1.0:
    - Penambahan fitur di filament:
        * Input API URL dengan default http://127.0.0.1:8000 dan fitur cek koneksi ke URL.
        * Sinkronisasi data sekolah (nama sekolah, alamat, lintang dan bujur) ke server.
        * Sinkronisasi data user admin sekolah dan guru (nama dan password) ke server, baik data baru maupun data lama.
- 1.0.0: first release

## Fitur
### General
- Unggah lisensi key (*.xtss)
- Content Reader: membaca konten materi yang telah diunggah oleh guru dari XTSS Web Online
- Jenis konten yang bisa dibaca: PDF, HTML, MP4
### Otentikasi:
1. Admin berfungsi:
    - Menambahkan pengguna admin atau guru
    - Pengaturan data sekolah (nama sekolah, alamat, dan koordinat sekolah)
    - Backup data yang sudah terinstall di XTSS online
    - Restore data dari file *.ztss yang sudah pernah di unggah ke aplikasi XTSS Smartboard Offline Player
2. Guru berfungsi:
    - Merubah akun diri sendiri (Nama, email, password)
    - Backup data milik diri sendiri yang sudah terinstall di XTSS online
    - Restore data dari file *.ztss yang sudah pernah di unggah ke aplikasi XTSS Smartboard Offline Player oleh guru itu sendiri
### Requirement
| Perangkat | Min. Kebutuhan |
|:---|:---|
| Operating System | Windows 11 |
| RAM | 4GB |
| Disk Space | 1GB (application only without content) |
| Recomended Disk Space | 128GB |


*Developed by [entoknakal](mailto:entoknakal@yahoo.com)*