@extends('layouts.app')

@section('content')
<div class="card">
  <div class="card-body">
    <h4 class="card-title">Form Identitas Jamaah KBIH Pandanaran</h4>
    <p class="card-description"> Silakan isi data diri Anda </p>
    <form class="forms-sample">
      <div class="row">
        <div class="col-md-12">
          <div class="form-group">
            <label for="inputNama"><strong>Nama</strong></label>
            <input type="text" class="form-control" id="inputNama" placeholder="Nama Lengkap">
          </div>
        </div>
      </div>
      
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="inputNIK"><strong>Nomor Identitas (NIK)</strong></label>
            <input type="text" class="form-control" id="inputNIK" placeholder="Nomor Induk Kependudukan">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="inputPaspor"><strong>Nomor Paspor</strong></label>
            <input type="text" class="form-control" id="inputPaspor" placeholder="Nomor Paspor (jika ada)">
          </div>
        </div>
      </div>
      
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label for="inputTempatLahir"><strong>Tempat Lahir</strong></label>
            <input type="text" class="form-control" id="inputTempatLahir" placeholder="Tempat Lahir">
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="inputTanggalLahir"><strong>Tanggal Lahir</strong></label>
            <input type="date" class="form-control" id="inputTanggalLahir">
          </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="inputTanggalLahir"><strong>Jenis Kelamin</strong></label>
                <input type="gender" class="form-control" id="inputGender">
            </div>
            
        </div>
      </div>
      
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="inputNamaBapak"><strong>Nama Bapak</strong></label>
            <input type="text" class="form-control" id="inputNamaBapak" placeholder="Nama Bapak">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="inputNamaIbu"><strong>Nama Ibu</strong></label>
            <input type="text" class="form-control" id="inputNamaIbu" placeholder="Nama Ibu">
          </div>
        </div>
      </div>
      
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label for="inputAlamat"><strong>Alamat</strong></label>
            <textarea class="form-control" id="inputAlamat" rows="3" placeholder="Alamat Lengkap"></textarea>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="inputKotaAsal"><strong>Kota Asal</strong></label>
            <input type="text" class="form-control" id="inputKotaAsal" placeholder="Kota Asal">
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="inputPekerjaan"><strong>Pekerjaan</strong></label>
            <input type="text" class="form-control" id="inputPekerjaan" placeholder="Pekerjaan">
          </div>
        </div>
      </div>
      
      <button type="submit" class="btn btn-primary mr-2">Submit</button>
      <button type="reset" class="btn btn-light">Reset</button>
    </form>
  </div>
</div>



@endsection
