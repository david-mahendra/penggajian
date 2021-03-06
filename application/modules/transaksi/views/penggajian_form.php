<?php echo jquery_slimscrool(); ?>
<?php echo jquery_select2(); ?>
<?php echo bootstrap_datepicker3(); ?>

<script type="text/javascript">
$().ready(function() {
	$(".input-group.date").datepicker({ 
		autoclose: true, 
		todayHighlight: true 
	});
});	
</script>
<script type="text/javascript">
var perkiraan_arr 	= new Array();
var perkiraan_index = 0;
var num_unit = 0;

var jumlah_total = 0;

function add_penggajian_detail(e){
	var id_perkiraan = $(this).attr('href');
	var kode = $(this).attr('data-kode');
	var nama = $(this).attr('data-nama');
	var status = $(this).attr('data-status');
	var elemen = '';
	var selektor = '';

	var gajitotal=$('[name=gajitotal]').val();
	var tunj_jabatan_val=$('[name=tunj_jabatan]').val();
	var hari_kerja_sebulan=$('[name=hari_kerja_sebulan]').val();
	var hari_kerja_2minggu=$('[name=hari_kerja_2minggu]').val();
	
	if (perkiraan_arr.indexOf(id_perkiraan) > -1) {
		alert('Nama perkiraan gaji sudah terdaftar di tabel transaksi gaji!');
		return false;
	}
	
	if(status == '1'){
		var elemen = 'pendapatan_arr';
		var selektor = 'pendapatan_arr';
	}
	else if(status == '0'){
		var elemen = 'potongan_arr';
		var selektor = 'potongan_arr';
	}

	if(nama == 'Gaji Pokok'){
		var isi_input = Math.ceil(gajitotal/hari_kerja_sebulan*hari_kerja_2minggu);
		var data_id = 'id="input-gaji-pokok"';
	} else if(nama =='Tunjangan Jabatan'){
		var isi_input = tunj_jabatan_val;
	} else if(nama =='BPJS'){
		var isi_input = 22000;
	} else if(nama =='Lembur'){
		var isi_input = 0;
		var data_id = 'id="input-lembur"';
	} else if(nama =='Uang Makan'){
		var lembur=$('#input-lembur').val();
		if ($('#input-lembur').length) {
			var isi_input = 12000;
		} else {
			var isi_input = 0;
		}
	} else {
		var isi_input = '';
		var data_id = '';
	}
	
	num_unit++;
	var perkiraan =
		'<tr id="indent-perkiraan-' + id_perkiraan + '">' +
			'<td>' + num_unit + '</td>' +
			'<td class="nama-perkiraan">' + nama + '</td>' + 
			'<td><input type="hidden" name="id_perkiraan[' + id_perkiraan + ']" value="' + id_perkiraan + '" /> <input type="text" name="' + elemen + '[' + id_perkiraan + ']" class="' + selektor + ' nominal-perkiraan" data-status="' + status + '" value="'+ isi_input +'" autocomplete="off" '+ data_id +' /></td>' +
			'<td align="center"><a href="#' + id_perkiraan + '" data-status="' + status + '" class="del"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a>' +
		'</tr>';
	
	$('#perkiraan-unit-table').append(perkiraan);
	$('#indent-perkiraan-'  + id_perkiraan + ' .del').on('click', delete_perkiraan_details);
	
	e.preventDefault();
	perkiraan_arr[perkiraan_index] = id_perkiraan;
	perkiraan_index++;
	change_jumlah();
	
	$('.pendapatan_arr').on('keydown', number_key);
	$('.pendapatan_arr').on('keyup', change_jumlah);
	$('.potongan_arr').on('keydown', number_key);
	$('.potongan_arr').on('keyup', change_jumlah);
	
	$('.pendapatan_arr, .potongan_arr').keyup(function(){
		$(this).val(numtocurrency($(this).val())); 
	});
}

function delete_perkiraan_details(){
	var href = $(this).attr('href');
	var href_arr = href.split('#');
	var id_perkiraan = href_arr[1];
	var status = $(this).attr('data-status');
	var total_pendapatan = 0;
	var total_potongan = 0;
	var gaji_bersih = 0;

	var selector = '#indent-perkiraan-' + id_perkiraan;
	$(selector).remove();
	
	$.each($('.pendapatan_arr'), function(key, obj) {
		total_pendapatan += currencytonum($(obj).val());
	});
	$('[name=pendapatan]').val(numtocurrency(total_pendapatan));
	
	$.each($('.potongan_arr'), function(key, obj) {
		total_potongan += currencytonum($(obj).val());
	});
	$('[name=potongan]').val(numtocurrency(total_potongan));

	gaji_bersih = total_pendapatan - total_potongan;
	$('[name=gaji_bersih]').val(numtocurrency(gaji_bersih));
	
	perkiraan_arr[perkiraan_arr.indexOf(id_perkiraan)] = 0;
	return false;
}

function number_key(e){
	var keyCode = e.keyCode || e.which; 

	if (
		keyCode == 8
		|| keyCode == 9
        || keyCode == 190
		|| (keyCode >= 48 && keyCode <= 57)
		|| (keyCode >= 96 && keyCode <= 105)
	) {
		// do nothing
	}
	else {
		e.preventDefault();
	}
}

function numtocurrency(num){
	num = num.toString().replace(/\$|\,/g, '');
	
	if (isNaN(num)) num = "0";
	
	sign = (num == (num = Math.abs(num)));
	num = Math.floor(num * 100 + 0.50000000001);
	cents = num % 100;
	num = Math.floor(num / 100).toString();
	
	if (cents == 0) cents = '';
	else if (cents < 10) cents = ".0" + cents;
	else cents = '.' + cents;
	
	//for (var i = 0; i < Math.floor((num.length - (1 + i)) / 3); i++)
	//	num = num.substring(0, num.length - (4 * i + 3)) + ',' + num.substring(num.length - (4 * i + 3));
	
	return (((sign) ? '' : '-') + num + cents );
}

function currencytonum(str){
	if (str == "") return 0;

	// replace all dot to blank
	str = str.replace(/\,/g, "");
	
	// str to int
	return parseFloat(str);
}

function change_jumlah(){
	var status = $(this).attr('data-status');
	var pendapatan = $('[name=pendapatan]').val();
	var potongan = $('[name=potongan]').val();
	var pendapatan_jml = parseInt(pendapatan.replace('.',''));
	var potongan_jml = parseInt(potongan.replace('.',''));
	var total_pendapatan = 0;
	var total_potongan = 0;
	var gaji_bersih = 0;
	
	$.each($('.pendapatan_arr'), function(key, obj) {
		total_pendapatan += currencytonum($(obj).val());
	});
	$('[name=pendapatan]').val(numtocurrency(total_pendapatan));
	
	$.each($('.potongan_arr'), function(key, obj) {
		total_potongan += currencytonum($(obj).val());
	});
	$('[name=potongan]').val(numtocurrency(total_potongan));

	gaji_bersih = total_pendapatan - total_potongan;
	$('[name=gaji_bersih]').val(numtocurrency(gaji_bersih));
}

$().ready(function(){

	$('[name=id_karyawan]').select2({width: '100%'}); 
	// $('.list-perkiraan').slimscroll({ height: '190px', alwaysVisible: true }); 
		
	$('[name=id_karyawan]').change(function() {
		var id_karyawan = $(this).val();
		var periode_dari = $('[name=periode_dari]').val();
		var periode_sampai = $('[name=periode_sampai]').val();
		if (id_karyawan != '') {
			$.post (
				'<?php echo site_url('/master_data/karyawan/ajax_karyawan_options'); ?>'
				, {
					id_karyawan : id_karyawan,
					periode_dari: periode_dari,
					periode_sampai: periode_sampai
				}
				, function(data) {
					var karyawan = data.split('#');
					$('[name=nama]').val(karyawan[1]);
					$('[name=alamat]').val(karyawan[2])
					$('[name=jabatan]').val(karyawan[3]);
					$('[name=gajitotal]').val(karyawan[4]);
					$('[name=tunj_jabatan]').val(karyawan[5]);
					$('[name=hari_kerja_2minggu]').val(karyawan[6]);
				}
			);
		}
	});
	
	$('#cariTglPres').click(function() {
		var id_karyawan = $('[name=id_karyawan]').val();
		var periode_dari = $('[name=periode_dari]').val();
		var periode_sampai = $('[name=periode_sampai]').val();
		if (id_karyawan != '') {
			$.post (
				'<?php echo site_url('/master_data/karyawan/ajax_presensi_options'); ?>'
				, {
					id_karyawan : id_karyawan,
					periode_dari: periode_dari,
					periode_sampai: periode_sampai
				}
				, function(data) {
					$('[name=hari_kerja_2minggu]').val(data);
					var hari_kerja_sebulan = $('[name=hari_kerja_sebulan]').val();
					var gajitotal = $('[name=gajitotal]').val();
					var isi_input = Math.ceil(gajitotal/hari_kerja_sebulan*data);
					$('#input-gaji-pokok').val(isi_input);
					change_jumlah();
				}
			);
		}
	});

	$('a.add').click(add_penggajian_detail);

	$('[name=hari_kerja_sebulan]').keyup(function() {
		var hari_kerja_sebulan = $(this).val();
		var hari_kerja_2minggu = $('[name=hari_kerja_2minggu]').val();
		var gajitotal = $('[name=gajitotal]').val();
		var isi_input = Math.ceil(gajitotal/hari_kerja_sebulan*hari_kerja_2minggu);
		$('#input-gaji-pokok').val(isi_input);
		change_jumlah();
	});
	$('[name=hari_kerja_2minggu]').keyup(function() {
		var hari_kerja_sebulan = $('[name=hari_kerja_sebulan]').val();
		var hari_kerja_2minggu = $(this).val();
		var gajitotal = $('[name=gajitotal]').val();
		var isi_input = Math.ceil(gajitotal/hari_kerja_sebulan*hari_kerja_2minggu);
		$('#input-gaji-pokok').val(isi_input);
		change_jumlah();
	});
});
</script>

<h3 class="page-header">Transaksi Penggajian (Harian)</h3>

<?php echo form_open($action, array('class' => 'form-horizontal row-form')); ?>
<div class="col-sm-6">
    <div class="form-group">
        <label class="col-sm-3 control-label input-sm lbl-left">Periode Dari</label>
		<div class="col-sm-8">
           	<div class="input-group date" data-date="" data-date-format="yyyy-mm-dd">
				<input class="form-control input-sm" type="text" name="periode_dari" placeholder="Periode Dari" value="<?php echo date('Y-m-d',strtotime("-14 days")); ?>" required readonly />
				<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
            </div>
		</div>
	</div>
    <div class="form-group">
        <label class="col-sm-3 control-label input-sm lbl-left">No. Slip</label>
		<div class="col-sm-8">
		  <input class="form-control input-sm" type="text" name="no_slip" placeholder="No. Slip" value="<?php echo no_slip(); ?>" readonly />
		</div>
	</div>
    <div class="form-group">
        <label class="col-sm-3 control-label input-sm lbl-left">Tanggal</label>
		<div class="col-sm-8">
		  <input class="form-control input-sm" type="text" name="tgl_gaji" placeholder="Tanggal" value="<?php echo date("Y-m-d"); ?>" readonly />
		</div>
	</div>
    <div class="form-group">
        <label class="col-sm-3 control-label input-sm lbl-left">Hari Kerja (Sebulan)</label>
		<div class="col-sm-8">
		  <input class="form-control input-sm" type="number" name="hari_kerja_sebulan" placeholder="Hari Kerja (Sebulan)" value="26" min="1" max="31" value="" id="hk_sebulan" />
		</div>
	</div>
    <div class="form-group">
        <label class="col-sm-3 control-label input-sm lbl-left">Hari Kerja (2 Minggu)</label>
		<div class="col-sm-8">
		  <input class="form-control input-sm" type="number" name="hari_kerja_2minggu" placeholder="Hari Kerja 2 Minggu" value="12" min="1" max="14" value="" id="hk_2minggu" />
		</div>
	</div>
	<input type="hidden" name="jam_kerja_2minggu" placeholder="Jam Kerja 2 Minggu" value="0" id="jk_2minggu" />
	<input type="hidden" name="jenis" placeholder="Jenis Penggajian" value="0" id="jenis" />
    <div class="form-group">
        <label class="col-sm-3 control-label input-sm lbl-left">Petugas</label>
		<div class="col-sm-8">
		  <input class="form-control input-sm" type="text" name="petugas" placeholder="Petugas" value="<?php echo @$this->session->userdata('pengguna')->nama; ?>" readonly />
		</div>
	</div>
</div>
<div class="col-sm-6">
    <div class="form-group">
        <label class="col-sm-3 control-label input-sm lbl-left">Periode Sampai</label>
		<div class="col-sm-7">
           	<div class="input-group date" data-date="" data-date-format="yyyy-mm-dd">
				<input class="form-control input-sm" type="text" name="periode_sampai" placeholder="Periode Sampai" value="<?php echo date('Y-m-d'); ?>" required readonly />
				<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
            </div>
		</div>
		<div class="col-sm-1">
			<a href="#" class="btn btn-default btn-sm button-blue" id="cariTglPres">Cari</a>
		</div>
	</div>
    <div class="form-group">
        <label class="col-sm-3 control-label input-sm lbl-left">Nama</label>
		<div class="col-sm-8">
			<select name="id_karyawan">
				<option value="">--- Pilih Karyawan ---</option>
				<?php echo modules::run('master_data/karyawan/options_karyawan'); ?>
			</select>
		</div>
	</div>
    <div class="form-group">
        <label class="col-sm-3 control-label input-sm lbl-left">Jabatan</label>
		<div class="col-sm-8">
		  <input class="form-control input-sm" type="text" name="jabatan" placeholder="Jabatan" value="" readonly />
		</div>
	</div>
    <div class="form-group">
        <label class="col-sm-3 control-label input-sm lbl-left">Alamat</label>
		<div class="col-sm-8">
			<textarea name="alamat" class="form-control" rows="3" placeholder="Alamat"></textarea>
		</div>
	</div>
	<input type="hidden" name="gajitotal" value="0">
	<input type="hidden" name="tunj_jabatan" value="0">
</div>

<div class="clearfix"></div>
<div class="border-tr"></div>

<?php if ($this->session->flashdata('cek_field') == 'failed') { ?>
	<div class="alert alert-danger alert-dismissible" role="alert">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>		
		<span>Data tidak berhasil disimpan!! Pastikan text No. Slip, Tanggal, dan ID Karyawan terisi</span>
	</div>
<?php } elseif ($this->session->flashdata('cek_field') == 'sukses') { ?>
	<div class="alert alert-success alert-dismissible" role="alert">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>		
		<span>Data berhasil disimpan kedalam database!!</span>
	</div>
<?php } ?>

<div class="col-sm-8">
	<div class="table-responsive table-transaksi">
		<table class="tabel table" id="perkiraan-unit-table">
			<thead>
				<tr>
					<th width="5%">No</th>
					<th width="60%">Nama Perkiraan</th>
					<th width="20%">Jumlah</th>
					<th width="15%">Action</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
</div>
<div class="col-sm-4">
	<div class="list-perkiraan" style="height: 190px;overflow: scroll;">
		<ul>
			<?php foreach($perkiraan as $p) : ?>
				<li><a class="add" href="<?php echo $p->id; ?>" data-kode="<?php echo $p->kode; ?>" data-nama="<?php echo $p->nama; ?>" data-status="<?php echo $p->status; ?>"><?php echo $p->nama; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>

<div class="clearfix"></div>
<div class="non-border-tr"></div>

<div class="col-sm-6">
    <div class="form-group">
        <label class="col-sm-3 control-label input-sm lbl-left">Pendapatan</label>
		<div class="col-sm-5">
		  <input class="form-control input-sm" type="text" name="pendapatan" placeholder="Pendapatan" value="" readonly />
		</div>
	</div>
    <div class="form-group">
        <label class="col-sm-3 control-label input-sm lbl-left">Potongan</label>
		<div class="col-sm-5">
		  <input class="form-control input-sm" type="text" name="potongan" placeholder="Potongan" value="" readonly />
		</div>
	</div>
    <div class="form-group">
        <label class="col-sm-3 control-label input-sm lbl-left">Gaji Bersih</label>
		<div class="col-sm-5">
		  <input class="form-control input-sm" type="text" name="gaji_bersih" placeholder="Gaji Bersih" value="" readonly />
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-6">
			<button type="submit" class="btn btn btn-primary btn-sm button-blue" > Simpan </button>
			<button type="reset" class="btn btn btn-primary btn-sm button-red" > Reset </button>
		</div>
	</div>
</div>
<?php echo form_close(); ?>