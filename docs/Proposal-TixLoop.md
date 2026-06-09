*Kode A


WEB TECHNOLOGY COMPETITION
TIXLOOP: PLATFORM RESALE TIKET UNTUK MENGURANGI PEMBOROSAN TIKET EVENT YANG TIDAK TERPAKAI AKIBAT KETIDAKHADIRAN PENONTON











Disusun Oleh:
Amru Dzaky Ramadhan (V3424087)
Rosyid Stania Ardiyan Putra (V3424075)
Tazkia Putri Hidayati (V3424079)


Dosen Pembimbing:
Agus Purbayu, S.Si., M.Kom



UNIVERSITAS SEBELAS MARET
KOTA SURAKARTA
2026
DAFTAR ISI
DAFTAR ISI	i
DAFTAR TABEL	iii
DAFTAR GAMBAR	iv
BAB I: PENDAHULUAN	1
1.1 Latar Belakang	1
1.2 Rumusan Masalah	2
1.3 Tujuan & Manfaat	2
1.4 Batasan Masalah	4
BAB II: SOLUSI & INOVASI	5
2.1 Solusi TixLoop	5
2.2 Inovasi Teknologi Utama	5
2.3 Target Pengguna & Keunggulan Komparatif	6
2.4 Keterkaitan dengan SDGs (Target 12.5)	7
BAB III: METODOLOGI PERANCANGAN SISTEM	7
3.1 Analisis Kebutuhan Data & Karakteristik Tiket	7
3.2 Arsitektur Sistem	8
3.3 Desain Perancangan Perangkat Lunak	9
BAB IV: HASIL DAN PEMBAHASAN	12
4.1 Implementasi Antarmuka Sistem (UI/UX)	12
1. Homepage Marketplace	13
2. Halaman Marketplace Tiket	14
3. Sistem Pencegahan Tiket Hangus	14
4. Waste Dashboard Preview	14
5. Alur Penjualan Tiket	15
4.2 Implementasi Fitur Inti Sistem	15
1. Sistem Pencegahan Tiket Hangus	15
2. Penerapan Batas Maksimal Harga	16
3. Escrow Protection Simulation	16
4. Verifikasi Tiket Hybrid	16
4.3 Simulasi Alur Sistem	17
1. Alur Penjualan Tiket	17
2. Alur Pembelian Tiket	17
3. Alur Sirkulasi Penggunaan Tiket	17
4.4 Visualisasi Waste Dashboard	17
4.5 Pembahasan Hasil Implementasi	18
BAB V: PENUTUP	19
5.1 Kesimpulan	19
5.2 Rencana Pengembangan Masa Depan	19
DAFTAR PUSTAKA	20

DAFTAR PUSTAKA	19


DAFTAR TABEL
Tabel 1. Keunggulan komaratif TixLoop dibanding pasar sekunder	6



DAFTAR GAMBAR
Gambar 1. Arsitektur Sistem	8
Gambar 2. Use Case Diagram	10
Gambar 3. Skema Diagram	11
Gambar 4. Desain Mockup	13


	




BAB I: PENDAHULUAN
1.1 Latar Belakang 
       Industri hiburan dan penyelenggaraan event di Indonesia mengalami lonjakan pertumbuhan yang sangat masif pasca-pandemi. Menurut Survei Industri Event Nasional 2024-2025 yang dirilis IVENDO (2025), pada tahun 2024 tercatat 8.777 event tersebar di 34 provinsi dengan total nilai bisnis mencapai Rp 84,46 triliun, melibatkan 8,8 juta tenaga kerja. Proyeksi tahun 2025 menunjukkan peningkatan menjadi Rp 88,62 triliun dengan rata-rata 9.209 event (IVENDO, 2025).
Platform ticketing utama seperti LOKET turut mencatat pertumbuhan eksponensial. Sejak 2022 hingga Juli 2025, LOKET telah memfasilitasi lebih dari 25.000 acara, dengan rata-rata penjualan tiket konser mencapai lebih dari 80 persen dari total kapasitas venue. Lonjakan ini didorong oleh permintaan tinggi akan pengalaman live, terutama konser musik, festival, dan event olahraga (IVENDO, 2025).
Namun, akselerasi pertumbuhan ini tidak diimbangi dengan infrastruktur manajemen tiket sekunder yang adil dan transparan. Kebijakan tiket yang bersifat non-refundable dan non-transferable secara resmi menyebabkan ketidak fleksibelan bagi konsumen. Ketika pemegang tiket mengalami keadaan darurat atau perubahan mendadak, tiket tersebut tidak dapat dikembalikan atau diuangkan melalui kanal resmi (Courty, 2003).
Fenomena tiket yang tidak terpakai bukanlah kasus yang bersifat insidental. Pada berbagai penyelenggaraan konser, festival, maupun pertandingan olahraga, selalu terdapat sebagian pemegang tiket yang berhalangan hadir akibat perubahan jadwal, kondisi kesehatan, keperluan mendadak, maupun faktor lainnya. Ketika tidak tersedia mekanisme penjualan kembali yang resmi dan aman, tiket tersebut kehilangan seluruh nilai ekonominya setelah acara berlangsung. Kondisi ini menunjukkan bahwa tiket event merupakan aset dengan masa berlaku yang sangat terbatas (perishable asset), sehingga memerlukan sistem sirkulasi yang mampu menjaga nilai manfaatnya hingga waktu pelaksanaan acara. Tanpa mekanisme tersebut, potensi pemborosan ekonomi akan terus meningkat seiring pertumbuhan industri event di Indonesia.
Ketiadaan mekanisme sirkulasi tiket resmi ini melahirkan dua masalah struktural yang besar. Pertama, maraknya praktik scalping di pasar gelap terutama di X/Twitter, Instagram, dan grup Telegram yang mendorong harga tiket hingga 200-500% dari harga resmi. Kedua, terjadinya economic waste dalam skala signifikan ketika ribuan tiket hangus tidak terpakai. Jika diasumsikan rata-rata 100 tiket hangus per event dengan harga rata-rata Rp500.000, maka dari 8.777 event yang diselenggarakan pada tahun 2024 berpotensi menimbulkan kerugian ekonomi berupa hilangnya nilai transaksi tiket yang tidak termanfaatkan dari sisi konsumen mencapai lebih dari Rp 438 miliar. Estimasi ini menunjukkan besarnya pemborosan sumber daya ekonomi dan lingkungan yang terjadi.
Fenomena ini semakin relevan dengan target SDG 12.5 yang menekankan pengurangan pemborosan kapasitas event dan nilai ekonomi tiket dari sisi konsumen melalui pencegahan, pengurangan, dan reuse. Tiket event, sebagai produk dengan nilai waktu terbatas, merupakan bentuk digital-physical waste yang dapat dicegah melalui mekanisme resale yang terverifikasi dan aman (Courty, 2003; United Nations, 2015).
1.2 Rumusan Masalah
Meskipun industri event di Indonesia tumbuh pesat, terdapat beberapa permasalahan mendasar yang belum terselesaikan secara sistemik:
1. Bagaimana cara mengatasi banyaknya tiket yang terbuang sia-sia karena memiliki masa berlaku terbatas, sementara kebijakan tiket yang tidak bisa dikembalikan (non-refundable) dan minimnya platform jual kembali resmi membuat pemilik tiket kesulitan menjual tiket yang tidak terpakai?
2. Bagaimana mengendalikan praktik scalping dan spekulasi harga di pasar sekunder liar yang menyebabkan harga tiket melambung hingga 200-500% dari harga resmi, sehingga menciptakan ketidakadilan akses bagi konsumen asli?
3. Bagaimana membangun kepercayaan dan keamanan transaksi di pasar resale tiket, mengingat tingginya kasus penipuan tiket palsu, double selling, dan kurangnya transparansi serta validasi kepemilikan tiket secara real-time?
4. Bagaimana mengintegrasikan prinsip circular economy dalam ekosistem tiket event agar tiket yang tidak digunakan oleh pembeli awal dapat dialihkan atau dijual kembali secara aman, terkontrol, dan transparan, tanpa mengganggu pendapatan maupun model bisnis penyelenggara acara? 
1.3 Tujuan & Manfaat
Tujuan utama dari proyek ini adalah membangun platform Circular Ticketing Marketplace bernama TixLoop berbasis arsitektur web modern (Laravel 13 dan SvelteKit). Platform ini dirancang untuk memfasilitasi penjualan kembali tiket event secara aman, transparan, dan terkendali dengan menerapkan prinsip circular economy.
Secara spesifik, tujuan proyek meliputi:
1. Menciptakan mekanisme resale tiket yang aman dan terverifikasi sehingga tiket yang sebelumnya tidak terpakai dapat dialihkan atau dijual kembali secara legal dan terkendali, tanpa mengganggu model bisnis penyelenggara event.
2. Menekan praktik scalping dan manipulasi harga di pasar sekunder melalui sistem pembatasan kenaikan harga (markup), verifikasi kepemilikan tiket, serta penggunaan sistem pembayaran escrow untuk meningkatkan keamanan transaksi. 
3. Membangun sistem validasi tiket secara real-time yang terintegrasi dengan Event Organizer guna menjamin keaslian tiket serta mencegah penipuan, seperti penjualan ganda (double selling) dan penggunaan tiket palsu. 
4. Mengurangi jumlah tiket yang hangus atau tidak terpakai (ticket waste) secara signifikan melalui teknologi rekomendasi harga dinamis dan mekanisme auto-recirculation tiket. 
5. Memberikan kontribusi terhadap pencapaian SDG 12.5 melalui pendekatan pencegahan limbah digital (digital waste prevention) dan optimalisasi pemanfaatan sumber daya pada penyelenggaraan event.
Manfaat bagi Konsumen/Pengguna
1. Memberikan perlindungan finansial bagi pemilik tiket yang berhalangan hadir dengan menyediakan mekanisme penjualan kembali tiket secara aman, cepat, dan terverifikasi sebelum tiket menjadi hangus.
2. Memberikan akses tiket bagi pembeli sekunder dengan harga yang lebih wajar dan transparan melalui pembatasan kenaikan harga (markup) serta sistem verifikasi transaksi. 
3. Menjamin keamanan transaksi melalui mekanisme pembayaran escrow dan validasi tiket secara real-time sehingga dapat meminimalkan risiko penipuan, seperti tiket palsu maupun penjualan ganda (double selling). 
Manfaat bagi Event Organizer
1. Meningkatkan tingkat keterisian venue melalui sirkulasi tiket resmi, sehingga mengoptimalkan pendapatan dari ticketing serta merchant di dalam venue.
2. Mendapatkan data analitik yang akurat dan real-time mengenai sirkulasi tiket sekunder.
3. Meningkatkan citra dan kepercayaan publik karena adanya kanal resale resmi yang mengurangi scalping liar dan tiket palsu.
4. Mendukung pengelolaan kapasitas event yang lebih baik dan berkelanjutan.
Manfaat bagi Masyarakat dan Industri Kreatif
1. Menciptakan pasar sekunder tiket yang adil, transparan, dan terkendali.
2. Mengurangi pemborosan ekonomi dan sumber daya event secara nasional.
3. Menjadi model inovasi system solution yang menggabungkan teknologi, ekonomi sirkular, dan keberlanjutan di industri hiburan Indonesia.
1.4 Batasan Masalah
1. Ruang Lingkup Jenis Event Sistem saat ini difokuskan hanya pada lima kategori tiket utama, yaitu Konser, Bioskop, Olahraga, Expo, dan Seminar/Workshop. Kategori event lainnya seperti tiket perjalanan (kereta, pesawat) atau tiket atraksi wisata belum termasuk dalam cakupan proyek tahap awal.
2. Peran sebagai Secondary Marketplace TixLoop tidak berfungsi sebagai primary ticketing issuing system. Platform ini hadir sebagai secondary ticketing marketplace yang berperan sebagai escrow secondary circulation ledger, yaitu memfasilitasi peredaran kembali tiket yang sudah diterbitkan oleh penyelenggara resmi.
3. Metode Verifikasi Tiket Proses verifikasi tiket fisik dan digital mengandalkan pendekatan hybrid verification, yang meliputi OCR (Optical Character Recognition) untuk parsing teks tiket serta verifikasi manual bertingkat oleh tim. Verifikasi sepenuhnya otomatis berbasis API dengan seluruh Event Organizer belum dapat diimplementasikan pada tahap awal pengembangan.
BAB II: SOLUSI & INOVASI
2.1 Solusi TixLoop
TixLoop hadir sebagai Circular Ticketing Marketplace pertama di Indonesia yang menerapkan model closed-loop marketplace. TixLoop tidak sekadar memfasilitasi resale tiket, tetapi mengubah tiket yang semula bersifat perishable good (mudah hangus) menjadi circular digital asset yang dapat terus bersirkulasi hingga digunakan secara optimal. Dengan filosofi utama  Keep Tickets Moving , platform ini mengintervensi rantai pasok tiket konvensional dengan mengubah tiket menjadi digital asset yang dapat disirkulasikan kembali secara aman, adil, dan terkendali (Courty, 2003).
Melalui TixLoop, tiket yang tidak terpakai oleh pemiliknya tidak lagi hangus, melainkan dialirkan kembali kepada penonton lain melalui sistem verifikasi ketat, proteksi harga, dan mekanisme escrow. Platform ini berfungsi sebagai secondary circulation ledger yang menghubungkan penjual, pembeli, dan penyelenggara event dalam satu ekosistem yang transparan, sehingga mewujudkan konsep ticket recirculation dalam kerangka circular economy (Courty, 2003; Geissdoerfer et al., 2017).
2.2 Inovasi Teknologi Utama
Solusi TixLoop membawa tiga pilar inovasi utama yang membedakannya dari platform ticketing existing di Indonesia:
1. Dynamic Fair Pricing Engine (Burn Prevention) Algoritma secara otomatis menyesuaikan harga tiket ketika event mendekati hari pelaksanaan untuk mengurangi risiko tiket hangus dan spekulasi harga. Nilai penurunan harga pada tahap MVP ini masih menggunakan pendekatan simulasi linear berdasarkan analisis data historis penjualan tiket. Pada tahap pengembangan lanjutan, mekanisme ini akan ditingkatkan menjadi adaptive demand-based pricing yang lebih dinamis, mempertimbangkan faktor seperti tingkat keterisian, permintaan real-time, dan perilaku pembeli. Mekanisme ini mencegah tiket hangus sekaligus mematikan praktik spekulasi harga oleh calo (Depken, 2007; Leslie & Sorensen, 2014).
2. Hard Price Cap Enforcement Sistem secara otomatis mengunci harga penawaran maksimal sebesar 115% dari harga nominal tiket (100% harga asli + 15% margin operasional). Setiap listing yang melebihi batas ini akan ditolak oleh sistem, sehingga mencegah harga tidak wajar dan scalping berlebihan (Depken, 2007). Mekanisme pembatasan markup harga ini merupakan bentuk anti-scalper mechanism yang bertujuan menciptakan fairness resale market (Courty & Pagliero, 2014).
3. Transparency Ledger dengan SHA-256 Setiap perubahan status kepemilikan, riwayat harga, dan transaksi tiket direkam dalam immutable audit trail untuk menjaga transparansi dan integritas data
4. Hybrid Ticket Verification System Pada tahap MVP, TixLoop menggunakan sistem hybrid verification yang menggabungkan teknologi OCR, verifikasi multi-tier (email, nomor telepon, dan selfie), serta manual review sebagai fallback mechanism. Sistem ini dirancang sebagai solusi sementara yang aman sebelum dilakukan integrasi API resmi secara bertahap dengan Event Organizer (EO) untuk verifikasi real-time. Pendekatan bertahap ini memastikan tingkat keamanan dan validitas tiket yang tinggi sekaligus menjaga skalabilitas platform di masa awal operasional.
2.3 Target Pengguna & Keunggulan Komparatif
Target pengguna utama TixLoop adalah generasi muda usia produktif 17-35 tahun yang aktif menghadiri event serta Event Organizer (EO) resmi.
Keunggulan komparatif TixLoop dibandingkan pasar sekunder liar disajikan dalam tabel berikut:
Tabel 1. Keunggulan komaratif TixLoop dibanding pasar sekunder
Fitur
Pasar Gelap/Media Sosial
TixLoop (Inovasi Kami)
Batasan Harga Jual
Tidak terbatas (bisa 200-500%)
Dikunci maksimal 115% dari harga asli
Keamanan Transaksi
Rentan penipuan (direct transfer)
Escrow System (Rekening Bersama)
Autentikasi Tiket
Hanya berdasarkan chat & kepercayaan.
Hybrid OCR + Verifikasi Multi-tier
Fluktuasi Mendekati Hari H
Dimanipulasi calo berdasarkan kepanikan
Turun otomatis & linier (Burn Prevention)
Transparansi Riwayat
Tidak ada
Immutable Ledger (SHA-256)
Validasi oleh Organizer
Tidak ada
Terintegrasi (real-time)
2.4 Keterkaitan dengan SDGs (Target 12.5)
TixLoop secara langsung mendukung SDG 12: Responsible Consumption and Production, khususnya Target 12.5 yang menekankan pengurangan limbah melalui pencegahan (prevention), pengurangan, dan penggunaan kembali (reuse) (United Nations, 2015).
Dengan mengubah tiket dari komoditas sekali pakai menjadi aset digital yang bersirkulasi, TixLoop berhasil menekan economic waste dan digital waste hingga ratusan miliar rupiah per tahun di industri kreatif. Platform ini tidak hanya memberikan solusi teknologi, tetapi juga menciptakan perubahan perilaku konsumsi yang lebih bertanggung jawab di kalangan penonton event Indonesia (United Nations, 2015).
BAB III: METODOLOGI PERANCANGAN SISTEM
3.1 Analisis Kebutuhan Data & Karakteristik Tiket
Sistem TixLoop dirancang untuk menangani berbagai kategori tiket seperti konser, bioskop, olahraga, expo, dan seminar melalui penggunaan JSON metadata yang fleksibel untuk mendukung perbedaan atribut serta kebutuhan validasi pada setiap event. Pendekatan schema-less ini memungkinkan sistem menampung atribut spesifik dari berbagai jenis event tanpa mengorbankan performa database. Selain itu, struktur tersebut mendukung skalabilitas sistem dalam penambahan kategori event baru tanpa perlu mengubah struktur tabel utama. 
3.2 Arsitektur Sistem
Proyek TixLoop dibangun menggunakan teknologi web modern terkini untuk menjamin performa, skalabilitas, dan keamanan tinggi.

Gambar 1. Arsitektur Sistem
1. Backend Layer: Menggunakan Laravel 13 sebagai headless RESTful API. Framework ini menangani seluruh business logic, service layer, form request validation, queue processing (untuk dynamic pricing), serta enkripsi dan audit logging.
2. Frontend Layer: Dibangun dengan SvelteKit yang dikompilasi menjadi Progressive Web App (PWA). Pendekatan ini memberikan pengalaman pengguna yang ringan dan responsif, loading super cepat, dan kemampuan offline untuk fitur scan tiket di lokasi event.
3. Security Layer: Mengimplementasikan PASETO (Platform-Agnostic Security Tokens) sebagai pengganti JWT/Sanctum. Security Layer menggunakan token-based authentication modern untuk meningkatkan keamanan transaksi dan sesi pengguna.
Arsitektur ini mengadopsi pola API-First dan Layered Architecture, memisahkan tanggung jawab antara presentation, business logic, dan data access layer untuk memudahkan maintenance dan skalabilitas di masa depan.
3.3 Desain Perancangan Perangkat Lunak
Desain sistem TixLoop menggunakan pendekatan berbasis UML dan Entity-Relationship Modeling untuk memastikan kejelasan, konsistensi, dan skalabilitas.


1. Use Case Diagram

Gambar 2. Use Case Diagram
Diagram Use Case memetakan dua aktor utama (Penjual dan Pembeli) yang harus melalui proses Identity Binding (verifikasi NIK KTP satu akun satu identitas) sebelum dapat mengakses fitur inti, yaitu:
1. postTicketResale() - Listing tiket untuk dijual
2. executeEscrowClaim() - Klaim dana setelah transaksi berhasil
3. validateTicketAtGate() - Verifikasi tiket di pintu masuk event
2. Skema Diagram
Skema basis data TixLoop dirancang menggunakan relational database berbasis MySQL untuk mendukung sistem marketplace resale tiket yang aman, terverifikasi, dan berkelanjutan.

Gambar 3. Skema Diagram
Sumber: TixLoop - Skema Diagram
Struktur basis data mencakup beberapa modul utama, yaitu:
1. manajemen pengguna dan verifikasi identitas,
2. pengelolaan event dan tiket,
3. resale listing dan dynamic pricing,
4. escrow transaction,
5. anti-scalper enforcement,
6. serta dashboard analitik waste prevention.
Relasi antar tabel memungkinkan sistem mencatat histori kepemilikan tiket, memvalidasi transaksi resale, dan mengelola perubahan harga secara otomatis melalui fitur Burn Prevention Engine. Selain itu, sistem juga mendukung monitoring sustainability melalui perhitungan tiket yang berhasil diselamatkan dan nilai ekonomi yang berhasil dipertahankan.

BAB IV: HASIL DAN PEMBAHASAN
4.1 Implementasi Antarmuka Sistem (UI/UX)

Gambar 4. Desain Mockup
Sumber : TixLoop - Figma
Implementasi antarmuka TixLoop dirancang menggunakan pendekatan marketplace-first UI dengan fokus utama pada keamanan transaksi, kemudahan navigasi, dan transparansi informasi tiket. Antarmuka dikembangkan menggunakan SvelteKit dan Tailwind CSS untuk menghasilkan tampilan yang responsif, modern, dan ringan di berbagai perangkat.
Desain sistem mengusung konsep dark modern marketplace yang memadukan elemen teknologi, trust system, dan visual sustainability agar pengguna dapat memahami fungsi platform hanya dalam beberapa detik pertama penggunaan.
Beberapa halaman utama yang telah diimplementasikan pada tahap prototipe meliputi:
1. Homepage Marketplace
Halaman utama difokuskan pada eksplorasi tiket resale, marketplace listing, statistik waste prevention, dan fitur anti-scalping. Pada halaman ini pengguna dapat langsung melihat daftar tiket yang tersedia, harga resale, indikator tiket terverifikasi, serta fitur Burn Prevention Engine.
2. Halaman Marketplace Tiket
Halaman marketplace menampilkan daftar tiket dari berbagai kategori event seperti konser, olahraga, seminar, festival, dan expo. Setiap kartu tiket (ticket card) menampilkan nama event, lokasi, waktu pelaksanaan, harga asli, harga resale, countdown menuju event, serta badge verifikasi penjual. Pendekatan ini bertujuan mempercepat proses pengambilan keputusan pengguna sekaligus meningkatkan rasa aman saat bertransaksi.
3. Sistem Pencegahan Tiket Hangus 
Fitur ini menjadi inovasi utama TixLoop. Sistem menampilkan simulasi penurunan harga otomatis ketika event mendekati hari pelaksanaan.
Contoh simulasi:
1. Harga awal : Rp2.000.000
2. H-72 : Rp1.850.000
3. H-48 : Rp1.700.000
4. H-24 : Rp1.550.000
Mekanisme ini bertujuan:
1. mengurangi tiket hangus,
2. meningkatkan peluang tiket terjual,
3. serta menjaga harga resale tetap wajar bagi pembeli.
4. Waste Dashboard Preview
Dashboard menampilkan visualisasi dampak ekonomi dan sustainability secara real-time menggunakan data simulasi.
Indikator utama meliputi:
1. Total Ticket Rescued
2. Rupiah Waste Prevented
3. Recovery Rate
4. Active Circular Transactions
Dashboard ini menjadi representasi implementasi SDG 12.5 pada industri ticketing digital.
5. Alur Penjualan Tiket 
Halaman penjualan tiket memungkinkan pengguna melakukan:
1. upload tiket,
2. pengisian data event,
3. penentuan harga,
4. dan preview listing sebelum dipublikasikan.
Pada tahap MVP, proses validasi tiket masih menggunakan pendekatan simulasi dan verifikasi manual terbatas.
4.2 Implementasi Fitur Inti Sistem
1. Sistem Pencegahan Tiket Hangus
Sistem Pencegahan Tiket Hangus merupakan algoritma utama yang dirancang untuk mencegah tiket hangus menjelang hari pelaksanaan event.
Sistem bekerja dengan:
1. mendeteksi waktu menuju event,
2. menghitung interval penurunan harga,
3. dan menerapkan penyesuaian harga otomatis setiap 8 jam pada H-72 menuju acara.
Tujuan utama fitur ini adalah:
1. mengurangi economic waste,
2. menjaga sirkulasi tiket tetap aktif,
3. serta meminimalkan spekulasi harga ekstrem.
2. Penerapan Batas Maksimal Harga 
TixLoop menerapkan pembatasan harga resale maksimum sebesar 115% dari harga asli tiket.
Sebagai contoh:
1. Harga asli tiket: Rp1.000.000
2. Maksimum harga resale: Rp1.150.000
Sistem akan otomatis menolak listing yang melebihi batas tersebut.
Fitur ini dirancang untuk:
1. menekan praktik scalping,
2. menjaga fairness market,
3. dan meningkatkan aksesibilitas tiket bagi pengguna asli.
3. Escrow Protection Simulation
Mekanisme escrow digunakan untuk meningkatkan keamanan transaksi antara penjual dan pembeli.
Alur kerja:
1. Pembeli melakukan pembayaran.
2. Dana ditahan sementara oleh sistem.
3. Tiket diverifikasi saat digunakan masuk venue.
4. Dana diteruskan kepada penjual setelah validasi berhasil.
Pada tahap prototipe, sistem escrow masih berupa simulasi alur transaksi dan belum terintegrasi langsung dengan payment gateway produksi.
4. Verifikasi Tiket Hybrid 
Proses verifikasi tiket menggunakan pendekatan hybrid:
1. OCR parsing,
2. QR extraction,
3. metadata validation,
4. dan verifikasi manual bertingkat.
Pendekatan ini dipilih karena tidak semua Event Organizer memiliki API validasi resmi.
4.3 Simulasi Alur Sistem
1. Alur Penjualan Tiket
1. Penjual melakukan login.
2. Penjual mengunggah tiket.
3. Sistem melakukan validasi awal.
4. Tiket masuk ke marketplace resale.
5. Pembeli melakukan transaksi.
6. Sistem escrow menahan dana sementara.
7. Tiket tervalidasi saat check-in venue.
8. Dana diteruskan kepada penjual.
2. Alur Pembelian Tiket
1. Pengguna mencari tiket melalui marketplace.
2. Pengguna memilih tiket yang diinginkan.
3. Sistem menampilkan detail dan status verifikasi tiket.
4. Pengguna melakukan pembayaran.
5. Tiket diterima secara digital.
6. Tiket digunakan untuk masuk venue.
3. Alur Sirkulasi Penggunaan Tiket 
Tiket yang tidak digunakan dapat dijual kembali melalui marketplace resale. Sistem memfasilitasi proses pemindahtanganan tiket secara aman, mulai dari verifikasi tiket, transaksi, hingga validasi penggunaan tiket oleh pengguna baru saat check-in venue. 
4.4 Visualisasi Waste Dashboard
Waste Dashboard dikembangkan sebagai fitur monitoring dampak sustainability TixLoop. Berdasarkan simulasi data MVP, sistem mampu menampilkan:
1. estimasi nilai ekonomi yang berhasil diselamatkan,
2. tingkat recovery tiket,
3. serta persentase pengurangan tiket hangus.
Contoh simulasi dashboard:
1. Total Ticket Rescued : 2.450 tiket
2. Rupiah Waste Prevented : Rp1,8 miliar
3. Recovery Rate : 42%
4. Active Resale Listings : 780 tiket
Visualisasi ditampilkan dalam bentuk:
1. progress chart,
2. marketplace analytics,
3. dan grafik recovery rate.
4.5 Pembahasan Hasil Implementasi
Hasil implementasi prototipe menunjukkan bahwa TixLoop memiliki potensi besar sebagai solusi circular ticketing marketplace di Indonesia.
Keunggulan utama sistem meliputi:
1. Pendekatan anti-scalping yang jelas melalui hard price cap.
2. Mekanisme Burn Prevention Engine yang mendukung pengurangan ticket waste.
3. Marketplace resale yang lebih aman melalui escrow dan verifikasi tiket.
4. Integrasi konsep circular economy dan SDG 12.5 secara langsung ke dalam model bisnis digital.
Selain itu, pendekatan marketplace-first UI membuat sistem terasa sebagai produk nyata yang siap dikembangkan lebih lanjut.
Namun, terdapat beberapa tantangan implementasi pada tahap pengembangan awal, antara lain:
1. integrasi API dengan berbagai Event Organizer,
2. kebutuhan validasi tiket real-time,
3. pengembangan sistem anti-fraud yang lebih kompleks,
4. dan edukasi pasar terhadap konsep circular ticketing.
Meskipun demikian, implementasi MVP TixLoop telah berhasil menunjukkan feasibility solusi, inovasi teknologi, serta relevansi kuat terhadap permasalahan ticket waste dan scalping di Indonesia.
BAB V: PENUTUP
5.1 Kesimpulan
TixLoop berhasil menjawab tuntutan tema OLIVIA XI 2026 dengan menghadirkan solusi teknologi informasi sirkular yang konkret terhadap pemborosan ekonomi akibat tiket hangus. Melalui integrasi sistem yang aman, escrow mechanism, dan hybrid ticket verification, TixLoop mampu mereduksi dominasi calo tiket sekaligus menjalankan misi pelestarian konsumsi berkelanjutan (SDG 12.5).

5.2 Rencana Pengembangan Masa Depan
Pengembangan berikutnya akan difokuskan pada peningkatan validasi tiket real-time dan penguatan keamanan transaksi untuk mendukung skalabilitas platform.


DAFTAR PUSTAKA
Courty, P. (2003). Some economics of ticket resale. Journal of Economic Perspectives, 17(2), 85-97. https://doi.org/10.1257/089533003765888431
Courty, P., & Pagliero, M. (2014). The pricing of event tickets. Journal of Economics & Management Strategy, 23(3), 645-670. https://doi.org/10.1111/jems.12062
Depken, C. A. (2007). Another look at anti-scalping laws: Theory and evidence. Public Choice, 130(1-2), 55-67. https://doi.org/10.1007/s11127-006-9088-5
Geissdoerfer, M., Savaget, P., Bocken, N. M. P., & Hultink, E. J. (2017). The circular economy - A new sustainability paradigm? Journal of Cleaner Production, 143, 757-768. https://doi.org/10.1016/j.jclepro.2016.12.048
IVENDO. (2025). Survei industri event nasional 2024-2025. Asosiasi Penyelenggara Event Nasional Indonesia.
Leslie, P., & Sorensen, A. (2014). Resale and rent-seeking: An application to ticket markets. The Review of Economic Studies, 81(1), 266-300. https://doi.org/10.1093/restud/rdt036
Lim, N. M. (2022). Dampak pandemi COVID-19 terhadap perkembangan event e-sports di Indonesia. Tourism Scientific Journal, 7(2), 208-222.
United Nations. (2015). Transforming our world: The 2030 agenda for sustainable development (A/RES/70/1). https://sdgs.un.org/2030agenda





2


2













2






2









2




