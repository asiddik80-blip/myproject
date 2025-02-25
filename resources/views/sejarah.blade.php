<!-- resources/views/sejarah.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Sejarah') }}
        </h2>
    </x-slot>

    <button id="sweetalert-btn">Tampilkan SweetAlert</button>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3>Sejarah Kota Makkah</h3>
                <p>Makkah, kota suci umat Islam di Arab Saudi, memiliki sejarah panjang yang menjadi pusat spiritual dan budaya. Berikut ini adalah beberapa poin penting dalam sejarah Makkah:

Asal Usul Makkah: Makkah diyakini telah ada sejak zaman Nabi Ibrahim (Abraham) dan putranya, Nabi Ismail. Kisahnya dimulai ketika Ibrahim membawa Hajar dan Ismail ke lembah gersang Makkah. Setelah kehabisan air, Hajar berlari antara bukit Safa dan Marwah untuk mencari air, hingga akhirnya, sumur Zamzam muncul secara mukjizat untuk memenuhi kebutuhan mereka.

Pembangunan Ka'bah: Ka'bah, bangunan suci di pusat Masjidil Haram, dibangun oleh Nabi Ibrahim dan Ismail sebagai tempat ibadah kepada Allah. Ka'bah menjadi kiblat umat Islam dan lambang penyembahan kepada Tuhan Yang Maha Esa. Makkah kemudian berkembang menjadi pusat perdagangan, menarik para pedagang dari seluruh wilayah.

Zaman Jahiliyah: Sebelum kedatangan Islam, Makkah dikuasai oleh suku Quraisy dan dikenal sebagai pusat perdagangan dan keagamaan. Namun, penyembahan berhala meluas, dan Ka'bah dipenuhi dengan berbagai patung dan simbol keagamaan dari berbagai suku.

Kelahiran Nabi Muhammad: Nabi Muhammad SAW lahir di Makkah sekitar tahun 570 Masehi. Di kota ini pula beliau menerima wahyu pertama di Gua Hira pada usia 40 tahun, yang menjadi awal mula ajaran Islam. Dakwah beliau di Makkah awalnya mengalami banyak penentangan dari suku Quraisy, hingga akhirnya beliau dan para pengikutnya berhijrah ke Madinah pada tahun 622 M.

Penaklukan Makkah: Setelah bertahun-tahun di Madinah, Nabi Muhammad kembali ke Makkah dengan pasukannya pada tahun 630 M dalam peristiwa Fathul Makkah. Penaklukan ini terjadi tanpa kekerasan besar, dan beliau membersihkan Ka'bah dari berhala-berhala, mengembalikannya sebagai tempat ibadah kepada Allah.

Peran Sebagai Kota Suci: Setelah penaklukan, Makkah menjadi pusat keagamaan yang dipandang suci oleh umat Islam. Ibadah haji ke Makkah kemudian diwajibkan bagi umat Islam yang mampu secara fisik dan finansial, menjadikannya salah satu dari lima rukun Islam.

Perkembangan Modern: Sejak berdirinya Kerajaan Arab Saudi pada abad ke-20, Makkah mengalami berbagai pengembangan untuk memfasilitasi jemaah haji dan umrah yang datang dari seluruh dunia. Pembangunan Masjidil Haram terus diperluas, dan kota ini dilengkapi dengan berbagai fasilitas modern, termasuk transportasi, akomodasi, dan infrastruktur kesehatan.

Makkah Saat Ini: Saat ini, Makkah menjadi destinasi ibadah utama bagi jutaan Muslim setiap tahun, baik untuk menunaikan haji maupun umrah. Pemerintah Arab Saudi terus berupaya memperluas dan memperbarui kota ini agar dapat menampung lebih banyak jemaah dengan aman dan nyaman.

Makkah tidak hanya memiliki makna religius, tetapi juga menjadi saksi sejarah panjang tentang perkembangan peradaban, spiritualitas, dan keimanan umat manusia.</p>
            </div>
        </div>
    </div>
</x-app-layout>



<script>
document.getElementById('sweetalert-btn').addEventListener('click', function() {
    Swal.fire({
        title: 'Berhasil!',
        text: 'SweetAlert sudah terpasang di Laravel Jetstream.',
        icon: 'success',
        confirmButtonText: 'OK'
    });
});
</script>

