# Security Policy

## Supported status

Project ini masih berada pada tahap local pre-staging. Belum ada deployment production yang dinyatakan didukung melalui repositori ini.

## Melaporkan kerentanan

Laporkan dugaan kerentanan melalui kanal privat pemilik proyek: **[PERLU DIKONFIRMASI: alamat email atau private security advisory repository]**.

Jangan membuat public issue yang berisi:

- token, password, private key, cookie, atau credential integrasi;
- data pasien, rekam medis, identitas personal, log privat, atau hasil ekspor;
- langkah eksploitasi aktif yang belum memiliki mitigasi.

Sertakan versi/commit, dampak, prasyarat, dan reproduksi minimum menggunakan data sintetis. Pemilik proyek akan mengonfirmasi penerimaan, melakukan triase, dan mengoordinasikan disclosure. Jangan menguji sistem milik pihak lain atau environment nyata tanpa izin tertulis.

## Menangani secret yang terpapar

Jika secret aktif diduga pernah masuk Git, hentikan penggunaannya, rotasi/revoke melalui penyedia terkait, audit akses, lalu evaluasi pembersihan history. Menghapus secret dari commit terbaru saja tidak menghilangkannya dari riwayat.

Kebijakan rinci terdapat di [public repository threat model](docs/07-security/PUBLIC-REPOSITORY-THREAT-MODEL.md).
