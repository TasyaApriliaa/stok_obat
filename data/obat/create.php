<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
/**
 * @var $connection PDO
 */

require_once('../../config/koneksi.php');

/**
 * Get input data POST
 */
$idObat = $_POST['id_obat'] ?? '';
$namaObat = $_POST['nama_obat'] ?? '';
$gambarObat = $_FILES['gambar_obat']['name'] ?? '';
$hargaJual =$_POST['harga_jual'] ?? '';
$hargaBeli = $_POST['harga_beli'] ?? '';
$stok = $_POST['stok'] ?? '';
$isOk = false;



/**
 * Method OK
 * Validation OK
 * Prepare query
 */

if($gambarObat != "") {
    $ekstensi_diperbolehkan = array('png','jpg'); //ekstensi file gambar yang bisa diupload
    $x = explode('.', $gambarObat); //memisahkan nama file dengan ekstensi yang diupload
    $ekstensi = strtolower(end($x));
    $file_tmp = $_FILES['gambar_obat']['tmp_name'];
    $angka_acak     = rand(1,999);
    $nama_gambar_baru = $angka_acak.'-'.$gambarObat; //menggabungkan angka acak dengan nama file sebenarnya
    if(in_array($ekstensi, $ekstensi_diperbolehkan) === true)  {
        move_uploaded_file($file_tmp, 'gambar/'.$nama_gambar_baru); //memindah file gambar ke folder gambar
        // jalankan query INSERT untuk menambah data ke database pastikan sesuai urutan (id tidak perlu karena dibikin otomatis)
        try{
            $fields = [];
            $query = "INSERT INTO obat  (id_obat , nama_obat , gambar_obat , harga_jual , harga_beli , stok) VALUES (:id_obat, :nama_obat, :gambar_obat, :harga_jual, :harga_beli, :stok )";
            $statement = $connection->prepare($query);
            /**
             * Bind params
             */
            $statement->bindValue(":id_obat", $idObat);
            $statement->bindValue(":nama_obat", $namaObat);
            $statement->bindValue(":gambar_obat", "$gambarObat");
            $statement->bindValue(":harga_jual", $hargaJual);
            $statement->bindValue(":harga_beli", $hargaBeli);
            $statement->bindValue(":stok", $stok);

            /**
             * Execute query
             */
            $isOk = $statement->execute();
        }catch (Exception $exception){
            $reply['error'] = $exception->getMessage();
            echo json_encode($reply);
            http_response_code(400);
            exit(0);
        }
        /**
         * If not OK, add error info
         * HTTP Status code 400: Bad request
         * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status#client_error_responses
         */
        if(!$isOk){
            $reply['error'] = $statement->errorInfo();
            http_response_code(400);
        }

    } else {
        //jika file ekstensi tidak jpg dan png maka alert ini yang tampil
        echo "Ekstensi gambar yang boleh hanya jpg atau png";
    }
}



/*
 * Get last data
 */
$lastId = $connection->lastInsertId();
$getResult = "SELECT * FROM obat WHERE id_obat = :id_obat";
$stm = $connection->prepare($getResult);
$stm->bindValue(':id_obat', $lastId);
$stm->execute();
$result = $stm->fetch(PDO::FETCH_ASSOC);


/**
 * Show output to client
 * Set status info true
 */
$reply['data'] = $result;
$reply['status'] = $isOk;
echo json_encode($reply);
