<?php $this->extend("layouts/backend"); ?>
<?php $this->section("content"); ?>
<template>
    <h1 class="font-weight-medium mb-2"><?= $title ?> &nbsp;<span class="font-weight-regular">{{startDate}} {{startDate != '' ? "&mdash;": ""}} {{endDate}}</span>
        <template>
            <v-menu v-model="menu" :close-on-content-click="false" offset-y>
                <template v-slot:activator="{ on, attrs }">
                    <v-btn icon v-bind="attrs" v-on="on">
                        <v-icon>mdi-calendar-filter</v-icon>
                    </v-btn>
                </template>
                <v-card width="250">
                    <v-card-text>
                        <p class="mb-1"><strong>Filter:</strong></p>
                        <div class="mb-3">
                            <a @click="hariini" title="Hari Ini" alt="Hari Ini">Hari Ini</a> &bull;
                            <a @click="tujuhHari" title="7 Hari Kemarin" alt="7 Hari Kemarin">7 Hari Kemarin</a> &bull;
                            <a @click="bulanIni" title="Bulan Ini" alt="Bulan Ini">Bulan Ini</a> &bull;
                            <a @click="tahunIni" title="Tahun Ini" alt="Tahun Ini">Tahun Ini</a> &bull;
                            <a @click="tahunLalu" title="Tahun Lalu" alt="Tahun Lalu">Tahun Lalu</a> &bull;
                            <a @click="reset" title="Reset" alt="Reset">Reset</a>
                        </div>
                        <p class="mb-1"><strong>Custom:</strong></p>
                        <p class="mb-1">Dari Tanggal - Sampai Tanggal</p>
                        <v-text-field v-model="startDate" type="date"></v-text-field>
                        <v-text-field v-model="endDate" type="date"></v-text-field>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn text @click="menu = false">
                            <?= lang('App.close'); ?>
                        </v-btn>
                        <v-btn color="primary" text @click="handleSubmit" :loading="loading">
                            Filter
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-menu>
        </template>
        <strong class="float-end">{{RibuanLocale(saldo)}}</strong>
    </h1>
    <v-card>
        <v-alert dense text type="primary" icon="mdi-bank-check">Bank Aktif
            <div class="font-weight-regular grey--text text--darken-3">
                <?= $bankAkun['nama_bank']; ?> -
                No. Rek: <strong><?= $bankAkun['no_rekening']; ?></strong>
                a.n <?= $bankAkun['bank_nama']; ?>
            </div>
        </v-alert>
        <v-card-title>
            <v-btn large color="primary" dark @click="modalAddOpen" elevation="1">
                <v-icon>mdi-plus</v-icon> <?= lang('App.add'); ?>
            </v-btn>
            <v-spacer></v-spacer>
            <v-text-field v-model="search" v-on:keydown.enter="handleSubmit" @click:clear="handleSubmit" append-icon="mdi-magnify" label="<?= lang('App.search') ?>" single-line hide-details clearable>
            </v-text-field>
        </v-card-title>

        <v-data-table :headers="dataTable" :items="data" :options.sync="options" :server-items-length="totalData" :items-per-page="10" :loading="loading" :search="search" loading-text="<?= lang('App.loadingWait'); ?>">
            <template v-slot:top>
                <v-toolbar flat>
                    <v-toolbar-title><v-icon class="mr-3">mdi-filter</v-icon></v-toolbar-title>
                    <v-select v-model="search" label="Filter Jenis" :items="dataJenis" item-text="text" item-value="value" @change="handleSubmit" single-line hide-details clearable class="mr-3" style="max-width: 250px;"></v-select>
                </v-toolbar>
            </template>
            <template v-slot:item="{ item }">
                <tr>
                    <td>{{item.faktur}}</td>
                    <td>{{dayjs(item.tanggal).format('DD-MM-YYYY')}} {{item.waktu}}</td>
                    <td>{{item.jenis}}</td>
                    <td>{{RibuanLocale(item.pemasukan)}}</td>
                    <td>{{RibuanLocale(item.pengeluaran)}}</td>
                    <td>{{item.keterangan}}</td>
                    <td>{{item.noref_nokartu ?? "-"}}</td>
                    <td>
                        <v-btn icon color="primary" class="mr-3" @click="setItem(item)" v-show="item.jenis == 'Pemasukan'" :disabled="item.keterangan == 'Transfered'" title="Mutasi" alt="Mutasi">
                            <v-icon>mdi-swap-horizontal-bold</v-icon>
                        </v-btn>
                        <span v-if="item.id_penjualan == null">
                            <v-btn icon color="error" @click="deleteItem(item)" title="Delete" alt="Delete">
                                <v-icon>mdi-delete</v-icon>
                            </v-btn>
                        </span>
                        <span v-else>
                            <v-btn icon color="error" title="Delete" alt="Delete" disabled>
                                <v-icon>mdi-delete</v-icon>
                            </v-btn>
                        </span>
                    </td>
                </tr>
            </template>
        </v-data-table>
    </v-card>
</template>

<!-- Modal Add -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalAdd" persistent max-width="600px">
            <v-card>
                <v-card-title><?= lang('App.add') ?> Bank
                    <v-spacer></v-spacer>
                    <v-btn icon @click="modalAddClose">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text class="py-5">
                    <v-form v-model="valid" ref="form">
                        <v-row>
                            <v-col>
                                <v-menu ref="date" v-model="date" :close-on-content-click="false" transition="scale-transition">
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-text-field v-model="tanggal" label="Pilih Tanggal" prepend-inner-icon="mdi-calendar" readonly v-bind="attrs" v-on="on" :error-messages="tanggalError" outlined></v-text-field>
                                    </template>
                                    <v-date-picker v-model="tanggal" @input="date = false" color="primary"></v-date-picker>
                                </v-menu>
                            </v-col>
                            <v-col>
                                <v-menu ref="time" v-model="time" :close-on-content-click="false" :return-value.sync="waktu" transition="scale-transition" offset-y max-width="290px" min-width="290px">
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-text-field v-model="waktu" label="Pilih Waktu" prepend-inner-icon="mdi-clock-time-four-outline" readonly v-bind="attrs" v-on="on" :error-messages="waktuError" outlined></v-text-field>
                                    </template>
                                    <v-time-picker v-if="time" v-model="waktu" full-width @click:minute="$refs.time.save(waktu)" format="24hr"></v-time-picker>
                                </v-menu>
                            </v-col>
                        </v-row>

                        <v-select v-model="jenis" label="<?= lang('App.type'); ?>" :items="dataJenis" item-text="text" item-value="value" :error-messages="jenisError" outlined></v-select>

                        <v-text-field v-model="nominal" type="number" label="Nominal (Rp)" :error-messages="nominalError" outlined></v-text-field>

                        <v-textarea v-model="keterangan" label="<?= lang('App.description'); ?>" :error-messages="keteranganError" rows="3" outlined></v-textarea>
                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn large color="primary" @click="saveBank" :loading="loading1" elevation="1">
                        <v-icon>mdi-content-save</v-icon> <?= lang('App.save') ?>
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>
<!-- End Modal Add -->

<!-- Modal Edit -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalEdit" persistent max-width="600px">
            <v-card>
                <v-card-title>Bank - Mutasi ke Kas
                    <v-spacer></v-spacer>
                    <v-btn icon @click="modalEdit = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text class="py-5">
                    <v-form v-model="valid" ref="form">
                        <v-text-field v-model="idBank" label="ID" type="text" filled disabled></v-text-field>

                        <v-select v-model="jenis" label="<?= lang('App.type'); ?>" :items="dataJenis" item-text="text" item-value="value" :error-messages="jenisError" outlined readonly></v-select>

                        <v-text-field v-model="nominal" type="number" label="Nominal (Rp)" :error-messages="nominalError" outlined></v-text-field>

                        <v-textarea v-model="keterangan" label="<?= lang('App.description'); ?>" :error-messages="keteranganError" rows="3" outlined></v-textarea>
                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn large color="primary" @click="saveBank" :loading="loading1" elevation="1">
                        <v-icon>mdi-swap-horizontal-bold</v-icon> <?= lang('App.transferCash') ?>
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>
<!-- End Modal Edit -->

<!-- Modal Delete -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalDelete" persistent max-width="600px">
            <v-card class="pa-2">
                <v-card-title>
                    <v-icon color="error" class="mr-2" x-large>mdi-alert-octagon</v-icon> <?= lang('App.confirmDelete'); ?>
                </v-card-title>
                <v-card-text>
                    <div class="mt-5 py-5">
                        <h2 class="font-weight-regular"><?= lang('App.delConfirm'); ?></h2>
                    </div>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn text @click="modalDelete = false" large elevation="1"><?= lang('App.close'); ?></v-btn>
                    <v-btn color="red" dark @click="deleteBank" :loading="loading" elevation="1" large><?= lang('App.delete'); ?></v-btn>
                    <v-spacer></v-spacer>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>
<!-- End Modal Delete -->

<!-- Loading2 -->
<v-dialog v-model="loading2" hide-overlay persistent width="300">
    <v-card>
        <v-card-text class="pt-3">
            <?= lang('App.loadingWait'); ?>
            <v-progress-linear indeterminate color="primary" class="mb-0"></v-progress-linear>
        </v-card-text>
    </v-card>
</v-dialog>
<!-- -->

<?php $this->endSection("content") ?>

<?php $this->section("js") ?>
<script>
    // Mendapatkan Token JWT
    const token = JSON.parse(localStorage.getItem('access_token'));

    // Menambahkan Auth Bearer Token yang didapatkan sebelumnya
    const options = {
        headers: {
            "Authorization": `Bearer ${token}`,
            "Content-Type": "application/json"
        }
    };

    // Deklarasi errorKeys
    var errorKeys = []

    // Initial Data
    dataVue = {
        ...dataVue,
        modalAdd: false,
        modalEdit: false,
        modalDelete: false,
        modalShow: false,
        search: "",
        menu: false,
        startDate: "",
        endDate: "",
        dataBank: [],
        totalData: 0,
        data: [],
        options: {},
        dataBankAkun: [],
        dataTable: [{
            text: 'Faktur',
            value: 'faktur'
        }, {
            text: 'Tanggal',
            value: 'tanggal'
        }, {
            text: 'Jenis',
            value: 'jenis'
        }, {
            text: 'Pemasukan',
            value: 'pemasukan'
        }, {
            text: 'Pengeluaran',
            value: 'pengeluaran'
        }, {
            text: 'Keterangan',
            value: 'keterangan'
        }, {
            text: 'No.Ref/Kartu',
            value: 'noref_nokartu'
        }, {
            text: 'Aksi',
            value: 'actions',
            sortable: false
        }, ],
        idBank: "",
        date: false,
        time: false,
        tanggal: "",
        tanggalError: "",
        waktu: "",
        waktuError: "",
        jenis: "",
        jenisError: "",
        dataJenis: [{
            text: 'Pemasukan',
            value: 'Pemasukan'
        }, {
            text: 'Pengeluaran',
            value: 'Pengeluaran'
        }, {
            text: 'Mutasi ke Kas',
            value: 'Mutasi ke Kas'
        }],
        nominal: "",
        nominalError: "",
        keterangan: "",
        keteranganError: "",
        saldo: 0,
        idBankAkun: "<?= $idBankUtama; ?>",
    }

    // Vue Created
    // Created: Dipanggil secara sinkron setelah instance dibuat
    createdVue = function() {
        this.getBank();
        this.getBankAkun();
        this.getSaldo();
    }

    // Vue Watch
    // Watch: Sebuah objek dimana keys adalah expresi-expresi untuk memantau dan values adalah callback-nya (fungsi yang dipanggil setelah suatu fungsi lain selesai dieksekusi).
    watchVue = {
        ...watchVue,
        options: {
            handler() {
                this.getDataFromApi()
            },
            deep: true,
        },

        dataBank: function() {
            if (this.dataBank != '') {
                // Call server-side paginate and sort
                this.getDataFromApi();
            }
        }
    }

    // Vue Methods
    // Methods: Metode-metode yang kemudian digabung ke dalam Vue instance
    methodsVue = {
        ...methodsVue,
        // Server-side paginate and sort
        getDataFromApi() {
            this.loading = true
            this.fetchData().then(data => {
                this.data = data.items
                this.totalData = data.total
                this.loading = false
            })
        },
        fetchData() {
            return new Promise((resolve, reject) => {
                const {
                    sortBy,
                    sortDesc,
                    page,
                    itemsPerPage
                } = this.options

                let search = this.search ?? "".trim();

                let items = this.dataBank
                const total = items.length

                if (search == search.toLowerCase()) {
                    items = items.filter(item => {
                        return Object.values(item)
                            .join(",")
                            .toLowerCase()
                            .includes(search);
                    });
                } else {
                    items = items.filter(item => {
                        return Object.values(item)
                            .join(",")
                            .includes(search);
                    });
                }

                if (sortBy.length === 1 && sortDesc.length === 1) {
                    items = items.sort((a, b) => {
                        const sortA = a[sortBy[0]]
                        const sortB = b[sortBy[0]]

                        if (sortDesc[0]) {
                            if (sortA < sortB) return 1
                            if (sortA > sortB) return -1
                            return 0
                        } else {
                            if (sortA < sortB) return -1
                            if (sortA > sortB) return 1
                            return 0
                        }
                    })
                }

                if (itemsPerPage > 0) {
                    items = items.slice((page - 1) * itemsPerPage, page * itemsPerPage)
                }

                setTimeout(() => {
                    resolve({
                        items,
                        total,
                    })
                }, 100)
            })
        },
        // End Server-side paginate and sort

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

        // Modal Add Open
        modalAddOpen: function() {
            this.modalAdd = true;
        },
        modalAddClose: function() {
            this.jenis = "";
            this.nominal = "";
            this.keterangan = "";
            this.modalAdd = false;
            this.$refs.form.resetValidation();
        },

        // Filter Date
        reset: function() {
            this.startDate = "";
            this.endDate = "";
        },
        tujuhHari: function() {
            this.startDate = "<?= $tujuhHari; ?>";
            this.endDate = "<?= $hariini; ?>";
        },
        hariini: function() {
            this.startDate = "<?= $hariini; ?>";
            this.endDate = "<?= $hariini; ?>";
        },
        bulanIni: function() {
            this.startDate = "<?= $awalBulan; ?>";
            this.endDate = "<?= $akhirBulan; ?>";
        },
        tahunIni: function() {
            this.startDate = "<?= $awalTahun; ?>";
            this.endDate = "<?= $akhirTahun; ?>";
        },
        tahunLalu: function() {
            this.startDate = "<?= $awalTahunLalu; ?>";
            this.endDate = "<?= $akhirTahunLalu; ?>";
        },

        // Handle Submit Filter
        handleSubmit: function() {
            if (this.startDate != '' && this.endDate != '') {
                this.getBankFiltered();
                this.getSaldoFiltered();
                this.menu = false;
            } else {
                this.getBank();
                this.getSaldo();
                this.startDate = "";
                this.endDate = "";
                this.menu = false;
            }
        },

        // Get Bank
        getBank: function() {
            this.loading = true;
            axios.get(`<?= base_url() ?>api/bank?bank=${this.idBankAkun}`, options)
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        //this.snackbar = true;
                        //this.snackbarMessage = data.message;
                        this.dataBank = data.data;
                        //console.log(this.settingData);
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.dataBank = data.data;
                        this.data = data.data;
                    }
                })
                .catch(err => {
                    // handle error
                    console.log(err);
                    this.loading = false;
                    var error = err.response
                    if (error.data.expired == true) {
                        this.snackbar = true;
                        this.snackbarMessage = error.data.message;
                        setTimeout(() => window.location.href = error.data.data.url, 1000);
                    }
                })
        },

        // Get Bank Filter by IdBankAkun
        getBankFiltered: function() {
            this.loading = true;
            axios.get(`<?= base_url() ?>api/bank?tgl_start=${this.startDate}&tgl_end=${this.endDate}&bank=${this.idBankAkun}`, options)
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        //this.snackbar = true;
                        //this.snackbarMessage = data.message;
                        this.dataBank = data.data;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.dataBank = data.data;
                        this.data = data.data;
                    }
                })
                .catch(err => {
                    // handle error
                    console.log(err);
                    this.loading = false;
                    var error = err.response
                    if (error.data.expired == true) {
                        this.snackbar = true;
                        this.snackbarMessage = error.data.message;
                        setTimeout(() => window.location.href = error.data.data.url, 1000);
                    }
                })
        },

        // Get Bank Akun
        getBankAkun: function() {
            this.loading = true;
            axios.get('<?= base_url() ?>api/bank/akun/all', options)
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        //this.snackbar = true;
                        //this.snackbarMessage = data.message;
                        this.dataBankAkun = data.data;
                        //console.log(this.settingData);
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.dataBankAkun = data.data;
                    }
                })
                .catch(err => {
                    // handle error
                    console.log(err);
                    this.loading = false;
                    var error = err.response
                    if (error.data.expired == true) {
                        this.snackbar = true;
                        this.snackbarMessage = error.data.message;
                        setTimeout(() => window.location.href = error.data.data.url, 1000);
                    }
                })
        },

        // Get Saldo
        getSaldo: function() {
            this.loading = true;
            axios.get(`<?= base_url() ?>api/bank/saldo?bank=${this.idBankAkun}`, options)
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        //this.snackbar = true;
                        //this.snackbarMessage = data.message;
                        this.saldo = data.data;
                        //console.log(this.saldo);
                    } else {
                        //this.snackbar = true;
                        //this.snackbarMessage = data.message;
                        this.saldo = data.data;
                    }
                })
                .catch(err => {
                    // handle error
                    console.log(err);
                    this.loading = false;
                    var error = err.response
                    if (error.data.expired == true) {
                        this.snackbar = true;
                        this.snackbarMessage = error.data.message;
                        setTimeout(() => window.location.href = error.data.data.url, 1000);
                    }
                })
        },

        // Get Saldo Filtered
        getSaldoFiltered: function() {
            this.loading = true;
            axios.get(`<?= base_url() ?>api/bank/saldo?tgl_start=${this.startDate}&tgl_end=${this.endDate}&bank=${this.idBankAkun}`, options)
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        //this.snackbar = true;
                        //this.snackbarMessage = data.message;
                        this.saldo = data.data;
                        //console.log(this.saldo);
                    } else {
                        //this.snackbar = true;
                        //this.snackbarMessage = data.message;
                        this.saldo = data.data;
                    }
                })
                .catch(err => {
                    // handle error
                    console.log(err);
                    this.loading = false;
                    var error = err.response
                    if (error.data.expired == true) {
                        this.snackbar = true;
                        this.snackbarMessage = error.data.message;
                        setTimeout(() => window.location.href = error.data.data.url, 1000);
                    }
                })
        },

        // Save Bank
        saveBank: function() {
            this.loading1 = true;
            axios.post('<?= base_url(); ?>api/bank/save', {
                    tanggal: this.tanggal,
                    waktu: this.waktu,
                    jenis: this.jenis,
                    nominal: parseInt(this.nominal),
                    keterangan: this.keterangan,
                    id_bank: this.idBank
                }, options)
                .then(res => {
                    // handle success
                    this.loading1 = false
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.getBank();
                        this.getSaldo();
                        this.tanggal = "";
                        this.waktu = "";
                        this.jenis = "";
                        this.nominal = "";
                        this.keterangan = "";
                        this.modalAdd = false;
                        this.modalEdit = false;
                        this.$refs.form.resetValidation();
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        errorKeys = Object.keys(data.data);
                        errorKeys.map((el) => {
                            this[`${el}Error`] = data.data[el];
                        });
                        if (errorKeys.length > 0) {
                            setTimeout(() => this.notifType = "", 4000);
                            setTimeout(() => errorKeys.map((el) => {
                                this[`${el}Error`] = "";
                            }), 4000);
                        }
                        this.modalAdd = true;
                        this.$refs.form.validate();
                    }
                })
                .catch(err => {
                    // handle error
                    console.log(err);
                    this.loading1 = false;
                    var error = err.response
                    if (error.data.expired == true) {
                        this.snackbar = true;
                        this.snackbarMessage = error.data.message;
                        setTimeout(() => window.location.href = error.data.data.url, 1000);
                    }
                })
        },

        // Set Item
        setItem: function(item) {
            this.modalEdit = true;
            this.idBank = item.id_bank;
            this.tanggal = item.tanggal;
            this.jenis = 'Mutasi ke Kas';
            this.waktu = item.waktu;
            this.nominal = item.pemasukan;
            this.keterangan = item.keterangan;
        },

        // Get Item Delete
        deleteItem: function(item) {
            this.modalDelete = true;
            this.idBank = item.id_bank;
        },

        // Delete
        deleteBank: function() {
            this.loading = true;
            axios.delete(`<?= base_url() ?>api/bank/delete/${this.idBank}`, options)
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.getBank();
                        this.getSaldo();
                        this.modalDelete = false;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.modalDelete = true;
                    }
                })
                .catch(err => {
                    // handle error
                    console.log(err);
                    this.loading = false;
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