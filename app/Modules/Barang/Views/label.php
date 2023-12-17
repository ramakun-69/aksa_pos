<?php
function Ribuan($angka)
{
    $hasil_rupiah = number_format($angka, 0, ',', '.');
    return $hasil_rupiah;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Print Label</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/paper.css'); ?>">
    <!-- Set page size here: A5, A4 or A3 -->
    <!-- Set also "landscape" if you need -->
    <style>
        @page {
            size: A4
        }

        @media print {

            html,
            body {
                height: 100vh;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
            }
        }
    </style>
    <style>
        .box {
            position: relative;
            width: 200px !important;
            height: 150px !important;
            border: 1px solid #222;
        }

        .multiple {
            float: left;
            margin: 5px 10px;
            padding: 5px 0;
        }

        .box-container {
            padding: 0px 5px;
        }

        .barcode-container {
            position: absolute;
            left: 0;
            bottom: 0;
            padding: 5px 5px;
        }

        .price {
            font-size: 1.8rem;
            text-align: right;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        .rp {
            float: left;
            font-size: 1rem;
            position: relative;
            bottom: 1rem;
            font-weight: 400;
        }
    </style>
</head>

<body class="A4" onLoad="javascript:window.print();">
    <section class="sheet padding-10mm">
        <?php if ($jumlah == 1) { ?>
            <div class="box">
                <div class="box-container">
                    <small><?= $namaToko; ?></small><br />
                    <strong><?= $namaBarang; ?></strong>
                    Rp<h1 class="price"><?= Ribuan($hargaJual); ?></h1>
                    <div class="barcode-container">
                        <?php echo '<img src="data:image/png;base64,' . $barcode->getBarcodePNG($text, $tipe, 1, 50, array(1, 1, 1), true) . '" alt="barcode"   />'; ?>
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <?php
            for ($i = 1; $i <= $jumlah; $i++) { ?>
                <div class="box multiple">
                    <div class="box-container">
                        <small><?= $namaToko; ?></small><br />
                        <strong><?= $namaBarang; ?></strong>
                        <h1 class="price">
                            <p class="rp">Rp</p><?= Ribuan($hargaJual); ?>
                        </h1>
                        <div class="barcode-container">
                            <?php echo '<img src="data:image/png;base64,' . $barcode->getBarcodePNG($text, $tipe, 1, 50, array(1, 1, 1), true) . '" alt="barcode"   />'; ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </section>
</body>

</html>