<?php
function Ribuan($angka)
{

    $hasil_rupiah = number_format($angka, 0, ',', '.');
    return $hasil_rupiah;
}
?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="<?= base_url('assets/css/bootstrap.min.css'); ?>" rel="stylesheet">

    <title>Print PDF</title>
    <style>
        .container {
            padding-left: 10px;
        }

        table {
            border: 1px solid #424242;
            border-collapse: collapse;
            padding: 0;
        }

        th {
            background-color: #f2f2f2;
            color: black;
            padding: 15px;
        }

        tr,
        td {
            border-bottom: 1px solid #ddd;
            padding: 15px;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <!-- <img src="<?= base_url() . '/' . $logo; ?>" width="80" height="80" alt="Logo" style="float:left;margin-top: 10px;margin-right: 10px;"> -->
        <h1><?= $toko['nama_toko']; ?></h1>
        <?= $toko['alamat_toko']; ?> - Telp/WA: <?= $toko['telp']; ?> - Email: <?= $toko['email']; ?> - NIB: <?= $toko['NIB']; ?>
        <hr />
        <h1 align="center">Laporan <?= mediumdate_indo($tgl_start); ?> &mdash; <?= mediumdate_indo($tgl_end); ?></h1>
        <h4>Stok Opname</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th scope="col">Code Item</th>
                    <th scope="col">Barcode</th>
                    <th scope="col">Nama Barang</th>
                    <th scope="col">Stok</th>
                    <th scope="col">Stok Nyata</th>
                    <th scope="col">Selisih</th>
                    <th scope="col">Nilai</th>
                    <!-- <th scope="col">Keterangan</th> -->
                    <th scope="col">Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php foreach ($data as $row) : ?>
                    <tr>
                        <td width="25"><?= $row['kode_barang']; ?></td>
                        <td><?= $row['barcode']; ?></td>
                        <td width="50"><?= $row['nama_barang']; ?></td>
                        <td><?= $row['stok']; ?></td>
                        <td><?= $row['stok_nyata']; ?></td>
                        <td><?= $row['selisih']; ?></td>
                        <td>Rp<?= Ribuan($row['nilai']); ?></td>
                        <!-- <td width="50"><?= $row['keterangan']; ?></td> -->
                        <td><?= date('d-m-Y', strtotime($row['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>

</html>