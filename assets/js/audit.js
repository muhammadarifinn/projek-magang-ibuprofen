// Ketuk sel untuk mengganti jawaban: kosong -> Ya -> Tidak -> N/A -> kosong
(function () {
	var urutan = ['', 'Y', 'T', 'N'];
	var label = { '': '–', Y: 'Ya', T: 'Tidak', N: 'N/A' };
	var tombol = document.querySelectorAll('button.sel');
	for (var i = 0; i < tombol.length; i++) {
		tombol[i].addEventListener('click', function () {
			var masukan = this.parentNode.querySelector('input[type=hidden]');
			var berikut = urutan[(urutan.indexOf(masukan.value) + 1) % urutan.length];
			masukan.value = berikut;
			this.setAttribute('data-v', berikut);
			this.textContent = label[berikut];
		});
	}
})();
