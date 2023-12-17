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
    <title>Print Report Close Cashier</title>
    <style>
        html,
        body {
            margin: 0;
            padding: 10px;
            font-family: Arial, Helvetica, sans-serif;
        }

        @media print {
            @page {
                margin-left: 0px;
                margin-right: 0px;
                margin-top: 0px;
                margin-bottom: 0px;
            }

            html,
            body {
                margin: 0;
                padding: 0;
                font-family: Arial, Helvetica, sans-serif;
            }

            #printContainer {
                margin: left;
                padding: 10px;
                text-align: justify;
                font-size: 100%;
            }
        }
    </style>
</head>

<body onLoad="javascript:window.print();">
    <div id="printContainer">
        <div style="line-height: normal;">

            <div style="font-weight: bold;text-align:center;">
                <h3>LAPORAN TUTUP KASIR<br />
                    TRANSAKSI PENJUALAN</h3>
            </div>
            <hr />
            <div>
                <p>
                    Kasir: <?= $user['nama']; ?><br />
                    Waktu Buka: <?= date('d-M-Y', strtotime($data_open['tanggal'])); ?>, <?= date('H:i', strtotime($data_open['waktu'])); ?><br />
                    Waktu Tutup: <?= date('d-M-Y', strtotime($data_close['tanggal'])); ?>, <?= date('H:i', strtotime($data_close['waktu'])); ?>
                </p>
            </div>
            <hr />
            <div>
                <p>Modal Awal: <span style="float: right !important;">0</span></p>
            </div>
            <hr />
            <div>
                <p>
                    Cash: <span style="float: right !important;"><?= Ribuan($total_cash); ?></span><br />
                    Transfer Bank: <span style="float: right !important;"><?= Ribuan($total_bank); ?></span><br />
                    <?php
                    $total = (int)$total_cash + (int)$total_bank;
                    ?>
                    Total Penerimaan: <span style="float: right !important;"><?= Ribuan($total); ?></span>
                </p>
            </div>
            <hr />
            <div>
                <p>
                    Saldo Akhir: <span style="float: right !important;"><?= Ribuan($total); ?></span>
                </p>
            </div>
            <hr />
            <div>
                <p>
                    Transaksi Selesai: <span style="float: right !important;"><?= $trx_selesai; ?></span><br />
                    Transaksi Belum<br />Terbayar: <span style="float: right !important;"><?= $trx_belum_selesai; ?></span>
                </p>
            </div>
            <hr />
            <div>
                <p>
                    Transaksi Belum<br />Terbayar (Rp): <span style="float: right !important;"><?= Ribuan($total_credit); ?></span>
                </p>
            </div>
            <br />
            <div style="text-align:center;font-size: 12px;">
                Dicetak tanggal <?= date('d-m-Y H:i:s'); ?>
            </div>
        </div>
    </div>
</body>

</html>