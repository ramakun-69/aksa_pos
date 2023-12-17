<?php $this->extend("layouts/mobile/frontend"); ?>
<?php $this->section("content"); ?>
<!-- Form Pencarian -->
<template>
    <v-container :class="$vuetify.theme.dark ? '':'indigo lighten-5'" fluid>
        <div class="pt-7">
            <v-row align="center" justify="center">
                <v-col cols="11">
                    <v-text-field color="primary" v-model="pencarian" v-on:keydown.enter="cariBarang" label="Scan Barcode" prepend-inner-icon="mdi-magnify" :autofocus="true" height="60" solo :loading="loading" loader-height="3" hide-details append-icon="mdi-camera" @click:append="dialogCameraOpen" ref="inputRef"></v-text-field>
                    <h4 class="font-weight-regular mt-2 mb-3">Hint: <?= lang('App.scanHome'); ?><h4>
                </v-col>
            </v-row>
        </div>
    </v-container>

    <v-container>
        <div class="mt-3" v-show="result == true">
            <!-- Tampil Hasil Pencarian -->
            <h2 class="text-h5 font-weight-medium mb-4" v-show="result == true"><?= lang('App.searchResult'); ?> "{{pencarian}}" &nbsp;<v-btn text @click="clear" title="Clear" alt="Clear"><v-icon color="error">mdi-eraser</v-icon> Clear</v-btn>
            </h2>

            <v-alert text v-if="notifType != ''" border="left" :type="notifType">{{notifMessage}}</v-alert>

            <v-row>
                <v-col cols="12" md="4" v-for="item in dataBarang" :key="item.id_barang">
                    <v-card min-height="150" :title="item.nama_barang" :alt="item.nama_barang">
                        <v-card-title class="text-h6 mb-0">{{item.nama_barang}}</v-card-title>
                        <v-card-text>
                            <h3 class="font-weight-medium"><?= lang('App.price'); ?>: {{ RibuanLocale(item.harga_jual) }}</h3>
                            <h3 class="font-weight-regular">Kode: {{item.kode_barang}}</h3>
                            <h3 class="font-weight-regular">Barcode: {{item.barcode}}</h3>
                            <h3 class="font-weight-regular">SKU: {{item.sku ?? "-"}}</h3>
                            <h3 class="font-weight-regular">Merk: {{item.merk ?? "-"}}</h3>
                            <h3 class="font-weight-regular"><?= lang('App.stock'); ?>: {{item.stok}}</h3>
                            <h3 class="font-weight-regular">Status: {{item.active == "1" ? "<?= lang('App.available'); ?>":"<?= lang('App.notAvailable'); ?>"}}</h3>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
            <br />
        </div>

        <div class="mt-3">
            <!-- Barang Terbaru -->
            <h2 class="mt-2 mb-3 font-weight-medium"><?= lang('App.latestItem') ?></h2>
            <v-row v-if="show == true">
                <v-col v-for="n in 4" :key="n" cols="12" sm="3">
                    <v-skeleton-loader class="mx-auto" max-width="300" type="paragraph, heading"></v-skeleton-loader>
                </v-col>
            </v-row>
            <v-row v-masonry transition-duration="0.3s" item-selector=".item" class="masonry-container" v-if="show == false">
                <v-col v-masonry-tile class="item" v-for="item in barangTerbaru" :key="item.id_barang" cols="6" xs="6" sm="6" md="4" lg="3">
                    <v-card min-height="150" :title="item.nama_barang" :alt="item.nama_barang">
                        <v-img height="200" :src="'<?= base_url() ?>' + item.media_path" class="text-left pa-2" v-if="item.media_path != null">
                            <v-chip>
                                <?= lang('App.stock'); ?>:&nbsp;
                                <span class="font-weight-bold" title="<?= lang('App.available'); ?>" alt="<?= lang('App.available'); ?>" v-if="item.active == '1'">{{item.stok}}</span>
                                <span class="red--text font-weight-bold" title="<?= lang('App.notAvailable'); ?>" alt="<?= lang('App.notAvailable'); ?>" v-else>{{item.stok}}</span>
                            </v-chip>
                        </v-img>
                        <v-img height="200" src="<?= base_url('images/no_image.jpg') ?>" class="text-left pa-2" v-else>
                            <v-chip>
                                <?= lang('App.stock'); ?>:&nbsp;
                                <span class="font-weight-bold" title="<?= lang('App.available'); ?>" alt="<?= lang('App.available'); ?>" v-if="item.active == '1'">{{item.stok}}</span>
                                <span class="red--text font-weight-bold" title="<?= lang('App.notAvailable'); ?>" alt="<?= lang('App.notAvailable'); ?>" v-else>{{item.stok}}</span>
                            </v-chip>
                        </v-img>
                        <v-card-title class="text-subtitle-1">{{item.nama_barang}}</v-card-title>
                        <v-card-subtitle class="mb-0">
                            <v-icon small>mdi-tag-outline</v-icon> {{item.merk}}
                        </v-card-subtitle>
                        <v-card-text class="text-h6 font-weight-bold mt-n3">
                            {{ RibuanLocale(item.harga_jual) }}
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
            <br />
            <paginate :page-count="pageCount" :no-li-surround="true" :container-class="'v-pagination theme--light'" :page-link-class="'v-pagination__item v-btn'" :active-class="'v-pagination__item--active primary'" :disabled-class="'v-pagination__navigation--disabled'" :prev-link-class="'v-pagination__navigation'" :next-link-class="'v-pagination__navigation'" :click-handler="handlePagination" :prev-text="'<small>Prev</small>'" :next-text="'<small>Next</small>'">
            </paginate>
        </div>
    </v-container>
</template>

<!-- Start Modal Camera -->
<template>
    <v-row justify="center">
        <v-dialog v-model="dialogCamera" width="900" persistent scrollable>
            <v-card>
                <v-card-title>
                    Camera
                    <v-spacer></v-spacer>
                    <v-btn icon @click="dialogCameraClose">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                <qrcode-scanner @result="onScanCamera" />
                <!--<p class="error--text">{{ error }}</p>
                    <qrcode-stream :camera="camera" @decode="onDecode" @init="onInit"></qrcode-stream>-->
            </v-card>
        </v-dialog>
    </v-row>
</template>
<!-- End Modal Camera -->

<?php $this->endSection("content") ?>

<?php $this->section("js") ?>
<script>
    computedVue = {
        ...computedVue,
    }

    dataVue = {
        ...dataVue,
        dialogCamera: false,
        /*camera: "auto",
        error: '',*/
        pencarian: "",
        dataBarang: [],
        barangTerbaru: [],
        pageCount: 0,
        currentPage: 1,
        limitPage: 8,
        scan: false,
        result: false,
    }

    createdVue = function() {
        this.getBarangTerbaru();
    }

    watchVue = {
        pencarian: function() {
            if (this.scan == true) {
                this.cariBarang();
            }
        }
    }

    methodsVue = {
        ...methodsVue,
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

        // Scan Camera
        dialogCameraOpen: function() {
            this.dialogCamera = true;
            this.$refs.inputRef.reset();
        },

        dialogCameraClose: function() {
            this.dialogCamera = false;
        },

        async onScanCamera(decodedText, decodedResult) {
            this.pencarian = decodedText;
            this.dialogCamera = false;
            this.scan = true;
        },

        /*
        async onDecode(decodedString) {
            this.pencarian = decodedString;
            this.dialogCamera = false;
            this.camera = "off";
        },
        async onInit(promise) {
            try {
                await promise
            } catch (error) {
                if (error.name === 'NotAllowedError') {
                    this.error = "ERROR: you need to grant camera access permission"
                } else if (error.name === 'NotFoundError') {
                    this.error = "ERROR: no camera on this device"
                } else if (error.name === 'NotSupportedError') {
                    this.error = "ERROR: secure context required (HTTPS, localhost)"
                } else if (error.name === 'NotReadableError') {
                    this.error = "ERROR: is the camera already in use?"
                } else if (error.name === 'OverconstrainedError') {
                    this.error = "ERROR: installed cameras are not suitable"
                } else if (error.name === 'StreamApiNotSupportedError') {
                    this.error = "ERROR: Stream API is not supported in this browser"
                } else if (error.name === 'InsecureContextError') {
                    this.error = 'ERROR: Camera access is only permitted in secure context. Use HTTPS or localhost rather than HTTP.';
                } else {
                    this.error = `ERROR: Camera error (${error.name})`;
                }
            }
        },*/

        // Get Product
        // Search Barang
        clear: function() {
            this.pencarian = "";
            this.notifType = "";
            this.dataBarang = [];
            this.result = false;
            this.$refs.inputRef.focus();
        },

        cariBarang: function() {
            if (this.pencarian != '') {
                this.getDataBarang();
                this.scan = false;
            }
        },

        // Get Data Barang
        getDataBarang: function() {
            this.loading = true;
            axios.get(`<?= base_url(); ?>api/cari_barang?query=${this.pencarian}`)
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.notifType = "success";
                        this.notifMessage = data.message;
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.dataBarang = data.data;
                        this.result = true;
                    } else {
                        this.notifType = "error";
                        this.notifMessage = data.message;
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.dataBarang = data.data;
                        this.result = true;
                    }
                })
                .catch(err => {
                    // handle error
                    console.log(err);
                })
        },

        // Get barang Limit 8
        getBarangTerbaru: function() {
            this.show = true;
            axios.get(`<?= base_url(); ?>api/barang/terbaru?page=${this.currentPage}&limit=${this.limitPage}`)
                .then(res => {
                    // handle success
                    this.show = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.barangTerbaru = data.data;
                        this.pageCount = Math.ceil(data.total_page / data.per_page);
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.barangTerbaru = data.data;
                    }
                })
                .catch(err => {
                    // handle error
                    console.log(err);
                })
        },

        handlePagination: function(pageNumber) {
            this.show = true;
            axios.get(`<?= base_url(); ?>api/barang/terbaru?page=${pageNumber}&limit=${this.limitPage}`)
                .then((res) => {
                    this.show = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.barangTerbaru = data.data;
                        this.pageCount = Math.ceil(data.total_page / data.per_page);
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.barangTerbaru = data.data;
                    }
                })
                .catch(err => {
                    // handle error
                    console.log(err);
                })
        }
    }
</script>

<?php $this->endSection("js") ?>