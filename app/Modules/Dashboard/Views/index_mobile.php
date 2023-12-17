<?php $this->extend("layouts/mobile/backend"); ?>
<?php $this->section("content"); ?>
<template>
    <?php if (session()->getFlashdata('success')) { ?>
        <v-alert type="success" dismissible v-model="alert">
            <?= session()->getFlashdata('success') ?>
        </v-alert>
    <?php } ?>
    <v-row>
        <v-col lg="3" cols="sm" class="pb-2">
            <v-card <?php if (session()->get('role') == 1 || session()->get('role') == 3) { ?>link href="<?= base_url('penjualan'); ?>" <?php } ?> min-height="130px">
                <div class="pa-5">
                    <h2 class="text-h5 font-weight-medium mb-2"><?= lang('App.sales'); ?>
                        <v-icon x-large class="green--text text--lighten-1 float-right">mdi-cart</v-icon>
                    </h2>
                    <h5 class="font-weight-regular"><?= lang('App.today'); ?>: <?= $countTrxHariini; ?>, Total: {{RibuanLocale(<?= ($totalTrxHariini - $sisaPiutangHariini) ?? "0"; ?>)}}*</h5>
                    <h5 class="font-weight-regular"><?= lang('App.yesterday'); ?>: <?= $countTrxHarikemarin; ?>, Total: {{RibuanLocale(<?= ($totalTrxHarikemarin - $sisaPiutangHarikemarin) ?? "0"; ?>)}}*</h5>
                </div>
            </v-card>
        </v-col>

        <v-col lg="3" cols="sm" class="pb-2">
            <v-card <?php if (session()->get('role') == 1 || session()->get('role') == 3) { ?>link href="<?= base_url('barang'); ?>" <?php } ?> min-height="130px">
                <div class="pa-5">
                    <h2 class="text-h5 font-weight-medium mb-2"><?= lang('App.items'); ?> <v-icon x-large class="blue--text text--lighten-1 float-right">mdi-package</v-icon>
                    </h2>
                    <h1 class="pa-0 ma-0"><?= $jmlBarang; ?></h1>
                </div>
            </v-card>
        </v-col>

        <v-col lg="3" cols="6" class="pb-2">
            <v-card <?php if (session()->get('role') == 1 || session()->get('role') == 3) { ?>link href="<?= base_url('kontak'); ?>" <?php } ?> min-height="130px">
                <div class="pa-5">
                    <h2 class="text-h5 font-weight-medium mb-2"><?= lang('App.contact'); ?> <v-icon x-large class="orange--text text--lighten-1 float-right">mdi-account-group</v-icon>
                    </h2>
                    <h1 class="pa-0 ma-0"><?= $jmlKontak; ?></h1>
                </div>
            </v-card>
        </v-col>

        <v-col lg="3" cols="6" class="pb-2">
            <v-card <?php if (session()->get('role') == 1 || session()->get('role') == 3) { ?>link href="<?= base_url('user'); ?>" <?php } ?> min-height="130px">
                <div class="pa-5">
                    <h2 class="text-h5 font-weight-medium mb-2">User <v-icon x-large class="red--text text--lighten-1 float-right">mdi-account-multiple</v-icon>
                    </h2>
                    <h1 class="pa-0 ma-0"><?= $jmlUser; ?></h1>
                </div>
            </v-card>
        </v-col>
    </v-row>

    <v-row>
        <v-col md="6" cols="12" class="pb-2">
            <v-card>
                <div class="pa-4">
                    <h2 class="font-weight-medium mb-0"><?= lang('App.incomeToday'); ?> <v-icon x-large class="green--text text--lighten-1 float-right">mdi-swap-horizontal-bold</v-icon>
                    </h2>
                    <p class="mb-2"><?= lang('App.todayCashflow'); ?></p>
                    <v-tabs height="35">
                        <v-tab>Cash</v-tab>
                        <v-tab>Bank</v-tab>
                        <v-tab-item>
                            <h1 class="mt-3">{{RibuanLocale(<?= $kasMasuk ?? "0"; ?>)}}</h1>
                        </v-tab-item>
                        <v-tab-item>
                            <h1 class="mt-3">{{RibuanLocale(<?= $bankMasuk ?? "0"; ?>)}}</h1>
                        </v-tab-item>
                    </v-tabs>
                </div>
            </v-card>
        </v-col>
        <v-col md="6" cols="12" class="pb-2">
            <v-card>
                <div class="pa-4">
                    <h2 class="font-weight-medium mb-0"><?= lang('App.expenseToday'); ?> <v-icon x-large class="red--text text--lighten-1 float-right">mdi-swap-horizontal-bold</v-icon>
                    </h2>
                    <p class="mb-2"><?= lang('App.todayCashout'); ?></p>
                    <v-tabs height="35">
                        <v-tab>Cash</v-tab>
                        <v-tab>Bank</v-tab>
                        <v-tab-item>
                            <h1 class="mt-3">{{RibuanLocale(<?= $kasKeluar ?? "0"; ?>)}}</h1>
                        </v-tab-item>
                        <v-tab-item>
                            <h1 class="mt-3">{{RibuanLocale(<?= $bankKeluar ?? "0"; ?>)}}</h1>
                        </v-tab-item>
                    </v-tabs>
                </div>
            </v-card>
        </v-col>
    </v-row>

    <v-row>
        <v-col md="6" cols="12" class="pb-2">
            <v-card link href="<?= base_url('hutang'); ?>">
                <div class="pa-4">
                    <h2 class="font-weight-medium mb-3"><?= lang('App.debts'); ?> <v-icon x-large class="float-right">mdi-tag-arrow-right</v-icon>
                    </h2>
                    <h4 class="font-weight-regular">Belum Dibayar: <strong>{{RibuanLocale(<?= $sisaHutang ?? "0"; ?>)}}</strong></h4>
                    <h4 class="font-weight-regular">Akan Jatuh Tempo: <strong><?= $hutangAkanTempo ?? "0"; ?></strong></h4>
                    <h4 class="font-weight-regular">Jatuh Tempo Hari ini: <strong><?= $hutangTempoHariini ?? "0"; ?></strong></h4>
                    <h4 class="font-weight-regular">Lewat Jatuh Tempo: <strong><?= $hutangLewatTempo ?? "0"; ?></strong></h4>
                </div>
            </v-card>
        </v-col>
        <v-col md="6" cols="12" class="pb-2">
            <v-card link href="<?= base_url('piutang'); ?>">
                <div class="pa-4">
                    <h2 class="font-weight-medium mb-3"><?= lang('App.receivables'); ?> <v-icon x-large class="float-right">mdi-book-arrow-left</v-icon>
                    </h2>
                    <h4 class="font-weight-regular">Belum Dibayar: <strong>{{RibuanLocale(<?= $sisaPiutang ?? "0"; ?>)}}</strong></h4>
                    <h4 class="font-weight-regular">Akan Jatuh Tempo: <strong><?= $piutangAkanTempo ?? "0"; ?></strong></h4>
                    <h4 class="font-weight-regular">Jatuh Tempo Hari ini: <strong><?= $piutangTempoHariini ?? "0"; ?></strong></h4>
                    <h4 class="font-weight-regular">Lewat Jatuh Tempo: <strong><?= $piutangLewatTempo ?? "0"; ?></strong></h4>
                </div>
            </v-card>
        </v-col>
    </v-row>

</template>

<br />

<template>
    <v-card>
        <v-card-title><?= lang('App.todayTrx'); ?></v-card-title>
        <v-card-subtitle>{{ tanggal }}</v-card-subtitle>
        <v-card-text>
            <bar-chart1></bar-chart1>
        </v-card-text>
    </v-card>
</template>

<br />

<template>
    <v-row>
        <v-col>
            <v-card height="600px">
                <v-card-title>Last Login</v-card-title>
                <v-card-text class="overflow-auto" style="height: 500px;">
                    <v-simple-table dense>
                        <template v-slot:default>
                            <thead>
                                <tr>
                                    <th rowspan="2">
                                        User
                                    </th>
                                    <th class="text-center" colspan="2">
                                        Waktu
                                    </th>
                                </tr>
                                <tr>
                                    <th class="text-center">
                                        Login
                                    </th>
                                    <th class="text-center">
                                        Logout
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in dataLog" :key="item.email">
                                    <td>{{ item.email }}<br />{{ item.username }}</td>
                                    <td>{{ item.loggedin_at }}</td>
                                    <td>
                                        <div v-if="item.loggedout_at != null">
                                            {{item.loggedout_at}}
                                        </div>
                                        <div v-else>
                                            <v-chip color="green" text-color="white" label><v-icon small left>mdi-information-outline</v-icon> Online</v-chip>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </template>
                    </v-simple-table>
                </v-card-text>
            </v-card>
        </v-col>
        <v-col>
            <v-card height="600px">
                <v-card-title>
                    <?= lang('App.latestItem') ?>
                </v-card-title>
                <v-card-text class="overflow-auto" style="height: 500px;">
                    <v-simple-table>
                        <template v-slot:default>
                            <thead>
                                <tr>
                                    <th class="text-left">
                                        Nama
                                    </th>
                                    <th class="text-left">
                                        Harga
                                    </th>
                                    <th class="text-left">
                                        Stok
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in barang" :key="item.id_barang">
                                    <td><strong>{{ item.nama_barang }}</strong><br />Kode: {{ item.kode_barang }}<br />Barcode: {{ item.barcode }}<br />SKU: {{ item.sku ?? "-"}}</td>
                                    <td>{{ RibuanLocale(item.harga_jual) }}</td>
                                    <td>{{ item.stok }}</td>
                                </tr>
                            </tbody>
                        </template>
                    </v-simple-table>
                </v-card-text>
            </v-card>
        </v-col>
    </v-row>
</template>

<?php $this->endSection("content") ?>

<?php $this->section("js") ?>
<script>
    // function date
    function addZeroBefore(n) {
        return (n < 10 ? '0' : '') + n;
    }

    function number_format(number, decimals, dec_point, thousands_sep) {
        // *     example: number_format(1234.56, 2, ',', ' ');
        // *     return: '1 234,56'
        number = (number + '').replace(',', '').replace(' ', '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function(n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
        // Fix for IE parseFloat(0.55).toFixed(0) = 0;
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }

    // Mendapatkan Token JWT
    const token = JSON.parse(localStorage.getItem('access_token'));

    // Menambahkan Auth Bearer Token yang didapatkan sebelumnya
    const options = {
        headers: {
            "Authorization": `Bearer ${token}`,
            "Content-Type": "application/json"
        }
    };

    // Initial Data
    dataVue = {
        ...dataVue,
        alert: false,
        barang: [],
        pageCount: 0,
        currentPage: 1,
        tanggal: "",
        dataLog: []
    }

    // Vue Created
    createdVue = function() {
        this.alert = true;
        setTimeout(() => {
            this.alert = false
        }, 5000)

        setInterval(this.getDayDate, 1000);

        // Load getBarang
        this.getBarang();

        this.getLoginLog();

        // Chart.js 1
        Vue.component('bar-chart1', {
            extends: VueChartJs.Bar,
            mounted() {
                this.renderChart({
                    labels: ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '00'],
                    datasets: [{
                        data: JSON.parse('<?= json_encode($harian) ?>'),
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(255, 159, 64, 0.2)',
                            'rgba(255, 205, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(201, 203, 207, 0.2)',
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(255, 159, 64, 0.2)',
                            'rgba(255, 205, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(201, 203, 207, 0.2)'
                        ],
                        borderColor: [
                            'rgb(255, 99, 132)',
                            'rgb(255, 159, 64)',
                            'rgb(255, 205, 86)',
                            'rgb(75, 192, 192)',
                            'rgb(54, 162, 235)',
                            'rgb(153, 102, 255)',
                            'rgb(201, 203, 207)',
                            'rgb(255, 99, 132)',
                            'rgb(255, 159, 64)',
                            'rgb(255, 205, 86)',
                            'rgb(75, 192, 192)',
                            'rgb(54, 162, 235)',
                            'rgb(153, 102, 255)',
                            'rgb(201, 203, 207)'
                        ],
                        borderWidth: 1
                    }]
                }, {
                    maintainAspectRatio: false,
                    legend: {
                        display: false
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        },
                        xAxes: [{
                            gridLines: {
                                color: "rgb(234, 236, 244)",
                                zeroLineColor: "rgb(234, 236, 244)",
                                drawBorder: true,
                                borderDash: [2],
                                zeroLineBorderDash: [2]
                            },
                            ticks: {
                                maxTicksLimit: 31
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                stepSize: 1,
                                // Include a dollar sign in the ticks
                                callback: function(value, index, values) {
                                    return number_format(value);
                                }
                            },
                            gridLines: {
                                color: "rgb(234, 236, 244)",
                                zeroLineColor: "rgb(234, 236, 244)",
                                drawBorder: true,
                                borderDash: [2],
                                zeroLineBorderDash: [2]
                            }
                        }],
                    },
                    tooltips: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyFontColor: "#858796",
                        titleMarginBottom: 10,
                        titleFontColor: '#6e707e',
                        titleFontSize: 14,
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        intersect: false,
                        mode: 'index',
                        caretPadding: 10,
                        callbacks: {
                            label: function(tooltipItem, chart) {
                                var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                                return datasetLabel + '<?= lang('App.transaction'); ?>: ' + number_format(tooltipItem.yLabel);
                            }
                        }
                    }
                })
            }

        })
    }

    // Vue Methods
    methodsVue = {
        ...methodsVue,
        //Get Tanggal
        getDayDate: function() {
            const weekday = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
            const month = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            const today = new Date();
            const date = addZeroBefore(today.getDate()) + ' ' + month[today.getMonth()] + ' ' + today.getFullYear();
            let Hari = weekday[today.getDay()];
            const Tanggal = date;
            this.tanggal = Hari + ', ' + Tanggal;
        },

        // Format Ribuan Rupiah versi 1
        RibuanLocale(key) {
            const rupiah = 'Rp' + Number(key).toLocaleString('id-ID');
            return rupiah
        },

        // Format Ribuan Rupiah versi 2
        Ribuan(key) {
            const format = key.toString().split('').reverse().join('');
            const convert = format.match(/\d{1,3}/g);
            const rupiah = 'Rp' + convert.join('.').split('').reverse().join('');

            return rupiah;
        },

        // Get Login Log
        getLoginLog: function() {
            this.show = true;
            axios.get(`<?= base_url(); ?>api/loginlog/last10`, options)
                .then(res => {
                    // handle success
                    var data = res.data;
                    if (data.status == true) {
                        this.dataLog = data.data;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.dataLog = data.data;
                    }
                })
                .catch(err => {
                    // handle error
                    console.log(err);
                    var error = err.response
                    if (error.data.expired == true) {
                        this.snackbar = true;
                        this.snackbarMessage = error.data.message;
                        setTimeout(() => window.location.href = error.data.data.url, 1000);
                    }
                })
        },

        // Get Barang
        getBarang: function() {
            axios.get(`<?= base_url(); ?>api/barang/terbaru?page=1&limit=10`, options)
                .then(res => {
                    // handle success
                    var data = res.data;
                    if (data.status == true) {
                        this.barang = data.data;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.barang = data.data;
                    }
                })
                .catch(err => {
                    // handle error
                    console.log(err);
                    var error = err.response
                    if (error.data.expired == true) {
                        this.snackbar = true;
                        this.snackbarMessage = error.data.message;
                        setTimeout(() => window.location.href = error.data.data.url, 1000);
                    }
                })
        },

    }
</script>
<?php $this->endSection("js") ?>