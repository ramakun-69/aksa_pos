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
    </h1>
    <?php if (session()->getFlashdata('error')) { ?>
        <v-alert type="error" prominent dense border="right" elevation="1" icon="mdi-alert-octagon" class="text-h6 font-weight-regular" v-model="alert">
            <?= session()->getFlashdata('error') ?>
        </v-alert>
    <?php } ?>
    <v-card>
        <v-card-title>
            <v-btn large color="primary" dark @click="modalAddOpenShow" class="mr-3" elevation="1">
                <v-icon>mdi-store-check</v-icon> Open
            </v-btn>
            <v-btn large color="error" dark @click="modalAddCloseShow" elevation="1">
                <v-icon>mdi-store-off</v-icon> Close
            </v-btn>
            <v-spacer></v-spacer>
            <v-text-field v-model="search" v-on:keydown.enter="handleSubmit" @click:clear="handleSubmit" append-icon="mdi-magnify" label="<?= lang('App.search') ?>" single-line hide-details clearable @click:clear="handleSubmit">
            </v-text-field>
        </v-card-title>
        <v-data-table v-model="selected" item-key="id_shift_openclose" hide-select :headers="dataTable" :items="data" :options.sync="options" :server-items-length="totalData" :items-per-page="10" :loading="loading" :search="search" loading-text="<?= lang('App.loadingWait'); ?>">
            <template v-slot:top>

            </template>
            <template v-slot:item="{ item, isSelected, select }">
                <tr :class="isSelected ? 'grey lighten-2':''">
                    <!-- <td @click="toggle(isSelected,select,$event)">
                        <v-icon color="primary" v-if="isSelected">mdi-checkbox-marked</v-icon>
                        <v-icon v-else>mdi-checkbox-blank-outline</v-icon>
                    </td> -->
                    <td>{{item.type}}</td>
                    <td>{{dayjs(item.tanggal).format('DD-MM-YYYY')}}</td>
                    <td>{{item.waktu}}</td>
                    <td>{{item.id_shift}}</td>
                    <td>{{item.nama}}<br />{{item.email}}</td>
                    <td>{{RibuanLocale(item.jumlah_uang_kertas)}}</td>
                    <td>{{RibuanLocale(item.jumlah_uang_koin)}}</td>
                    <td>
                        <v-btn icon color="primary" class="mr-2" @click="editItem(item)" title="Edit" alt="Edit">
                            <v-icon>mdi-pencil</v-icon>
                        </v-btn>
                        <v-btn icon class="mr-2" @click="showNota(item)" v-show="item.type == 'close'">
                            <v-icon color="grey darken-3">mdi-printer</v-icon>
                        </v-btn>
                        <v-btn icon color="error" @click="deleteItem(item)" title="Delete" alt="Delete">
                            <v-icon>mdi-delete</v-icon>
                        </v-btn>
                    </td>
                </tr>
            </template>
        </v-data-table>
    </v-card>
</template>

<!-- Modal Add Open -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalAddOpen" persistent scrollable max-width="900px">
            <v-card>
                <v-card-title>Open Cashier
                    <v-spacer></v-spacer>
                    <v-btn icon @click="modalAddOpenClose">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text class="py-5">
                    <v-form v-model="valid" ref="form1">
                        <v-row>
                            <v-col cols="12" sm="4">
                                <v-select v-model="idShift" label="Shift" :items="dataShift" :item-text="dataShift =>`${dataShift.nama_shift} (${dataShift.jam_mulai} - ${dataShift.jam_selesai})`" item-value="id_shift" :error-messages="id_shiftError" :loading="loading1" outlined></v-select>
                                <v-text-field v-model="cashier" type="text" label="Cashier (Login)" outlined disabled></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="4">
                                <v-text-field v-model="tanggal" type="date" label="Tanggal" :error-messages="tanggalError" outlined></v-text-field>
                                <v-text-field v-model="waktu" type="time" label="Jam Open" :error-messages="waktuError" outlined></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="4">
                                <v-text-field v-model="jumlahUangKertas" label="Total Uang Kertas (Rp)" :error-messages="jumlah_uang_kertasError" outlined></v-text-field>
                                <v-text-field v-model="jumlahUangKoin" label="Total Uang Koin (Rp)" :error-messages="jumlah_uang_koinError" outlined></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row class="mt-n7">
                            <v-col cols="12" sm="6">
                                <v-card outlined>
                                    <v-card-text>
                                        <p>Uang Kertas (jumlah lembar)</p>
                                        <div class="mb-3">
                                            <v-text-field type="number" min="0" v-model="kertas100" label="100.000" hide-details dense outlined :error-messages="kertas100Error" @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field type="number" min="0" v-model="kertas50" label="50.000" hide-details dense outlined :error-messages="kertas50Error" @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field type="number" min="0" v-model="kertas20" label="20.000" hide-details dense outlined :error-messages="kertas20Error" @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field type="number" min="0" v-model="kertas10" label="10.000" hide-details dense outlined :error-messages="kertas10Error" @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field type="number" min="0" v-model="kertas5" label="5000" hide-details dense outlined :error-messages="kertas5Error" @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field type="number" min="0" v-model="kertas2" label="2000" hide-details dense outlined :error-messages="kertas2Error" @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field type="number" min="0" v-model="kertas1" label="1000" hide-details dense outlined :error-messages="kertas1Error" @focus="$event.target.select()"></v-text-field>
                                        </div>
                                    </v-card-text>
                                </v-card>
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-card outlined>
                                    <v-card-text>
                                        <p>Uang Koin (jumlah koin)</p>
                                        <div class="mb-3">
                                            <v-text-field type="number" min="0" v-model="koin1000" label="1000" hide-details dense outlined :error-messages="koin1000Error" @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field type="number" min="0" v-model="koin500" label="500" hide-details dense outlined :error-messages="koin500Error" @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field type="number" min="0" v-model="koin200" label="200" hide-details dense outlined :error-messages="koin200Error" @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field type="number" min="0" v-model="koin100" label="100" hide-details dense outlined :error-messages="koin100Error" @focus="$event.target.select()"></v-text-field>
                                        </div>
                                    </v-card-text>
                                </v-card>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-checkbox v-model="redirect" label="Redirect to Sales (POS)"></v-checkbox>
                    <v-spacer></v-spacer>
                    <v-btn large color="primary" @click="saveOpenClose" :loading="loading2" elevation="1">
                        <v-icon>mdi-content-save</v-icon> <?= lang('App.save') ?>
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>
<!-- End Modal Add Open -->

<!-- Modal Add Close -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalAddClose" persistent scrollable max-width="900px">
            <v-card>
                <v-card-title>Close Cashier
                    <v-spacer></v-spacer>
                    <v-btn icon @click="modalAddCloseClose">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text class="py-5">
                    <v-form v-model="valid" ref="form2">
                        <v-row>
                            <v-col cols="12" sm="4">
                                <v-select v-model="idShift" label="Shift" :items="dataShift" :item-text="dataShift =>`${dataShift.nama_shift} (${dataShift.jam_mulai} - ${dataShift.jam_selesai})`" item-value="id_shift" :error-messages="id_shiftError" :loading="loading1" @change="getShiftById" outlined></v-select>
                                <v-text-field v-model="cashier" type="text" label="Cashier (Login)" outlined disabled></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="4">
                                <v-text-field v-model="tanggal" type="date" label="Tanggal" :error-messages="tanggalError" outlined></v-text-field>
                                <v-text-field v-model="waktu" type="time" :label="'Jam Close ' + waktuClose" :error-messages="waktuError" outlined></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="4">
                                <v-text-field v-model="jumlahUangKertas" label="Total Uang Kertas (Rp)" :error-messages="jumlah_uang_kertasError" outlined></v-text-field>
                                <v-text-field v-model="jumlahUangKoin" label="Total Uang Koin (Rp)" :error-messages="jumlah_uang_koinError" outlined></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row class="mt-n7">
                            <v-col cols="12" sm="6">
                                <v-card outlined>
                                    <v-card-text>
                                        <p>Uang Kertas (jumlah lembar)</p>
                                        <div class="mb-3">
                                            <v-text-field type="number" min="0" v-model="kertas100" label="100.000" hide-details dense outlined :error-messages="kertas100Error" @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field type="number" min="0" v-model="kertas50" label="50.000" hide-details dense outlined :error-messages="kertas50Error" @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field type="number" min="0" v-model="kertas20" label="20.000" hide-details dense outlined :error-messages="kertas20Error" @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field type="number" min="0" v-model="kertas10" label="10.000" hide-details dense outlined :error-messages="kertas10Error" @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field type="number" min="0" v-model="kertas5" label="5000" hide-details dense outlined :error-messages="kertas5Error" @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field type="number" min="0" v-model="kertas2" label="2000" hide-details dense outlined :error-messages="kertas2Error" @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field type="number" min="0" v-model="kertas1" label="1000" hide-details dense outlined :error-messages="kertas1Error" @focus="$event.target.select()"></v-text-field>
                                        </div>
                                    </v-card-text>
                                </v-card>
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-card outlined>
                                    <v-card-text>
                                        <p>Uang Koin (jumlah koin)</p>
                                        <div class="mb-3">
                                            <v-text-field type="number" min="0" v-model="koin1000" label="1000" hide-details dense outlined :error-messages="koin1000Error" @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field type="number" min="0" v-model="koin500" label="500" hide-details dense outlined :error-messages="koin500Error" @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field type="number" min="0" v-model="koin200" label="200" hide-details dense outlined :error-messages="koin200Error" @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field type="number" min="0" v-model="koin100" label="100" hide-details dense outlined :error-messages="koin100Error" @focus="$event.target.select()"></v-text-field>
                                        </div>
                                    </v-card-text>
                                </v-card>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn large color="primary" @click="saveOpenClose" :loading="loading2" elevation="1">
                        <v-icon>mdi-content-save</v-icon> <?= lang('App.save') ?>
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>
<!-- End Modal Add Close -->

<!-- Modal Edit -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalEdit" persistent scrollable max-width="900px">
            <v-card>
                <v-card-title><?= lang('App.edit') ?> <?= $title; ?>
                    <v-spacer></v-spacer>
                    <v-btn icon @click="modalEditClose">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text class="py-5">
                    <v-form ref="form" v-model="valid">
                        <v-row>
                            <v-col cols="12" sm="4">
                                <v-select v-model="idShift" label="Shift" :items="dataShift" :item-text="dataShift =>`${dataShift.nama_shift} (${dataShift.jam_mulai} - ${dataShift.jam_selesai})`" item-value="id_shift" :error-messages="id_shiftError" :loading="loading1" @change="getShiftById" outlined></v-select>
                                <v-text-field v-model="cashier" type="text" label="Cashier (Login)" outlined disabled></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="4">
                                <v-text-field v-model="tanggal" type="date" label="Tanggal" :error-messages="tanggalError" outlined></v-text-field>
                                <v-text-field v-model="waktu" type="time" label="Jam Close" :error-messages="waktuError" outlined></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="4">
                                <v-text-field v-model="jumlahUangKertas" label="Total Uang Kertas (Rp)" :error-messages="jumlah_uang_kertasError" outlined></v-text-field>
                                <v-text-field v-model="jumlahUangKoin" label="Total Uang Koin (Rp)" :error-messages="jumlah_uang_koinError" outlined></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row class="mt-n7">
                            <v-col cols="12" sm="6">
                                <v-card outlined>
                                    <v-card-text>
                                        <p>Uang Kertas (jumlah lembar)</p>
                                        <div class="mb-3">
                                            <v-text-field v-model="kertas100" label="100.000" hide-details dense outlined @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field v-model="kertas50" label="50.000" hide-details dense outlined @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field v-model="kertas20" label="20.000" hide-details dense outlined @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field v-model="kertas10" label="10.000" hide-details dense outlined @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field v-model="kertas5" label="5000" hide-details dense outlined @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field v-model="kertas2" label="2000" hide-details dense outlined @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field v-model="kertas1" label="1000" hide-details dense outlined @focus="$event.target.select()"></v-text-field>
                                        </div>
                                    </v-card-text>
                                </v-card>
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-card outlined>
                                    <v-card-text>
                                        <p>Uang Koin (jumlah koin)</p>
                                        <div class="mb-3">
                                            <v-text-field v-model="koin1000" label="1000" hide-details dense outlined @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field v-model="koin500" label="500" hide-details dense outlined @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field v-model="koin200" label="200" hide-details dense outlined @focus="$event.target.select()"></v-text-field>
                                        </div>
                                        <div class="mb-3">
                                            <v-text-field v-model="koin100" label="100" hide-details dense outlined @focus="$event.target.select()"></v-text-field>
                                        </div>
                                    </v-card-text>
                                </v-card>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn large color="primary" @click="updateOpenClose" :loading="loading" elevation="1">
                        <v-icon>mdi-content-save</v-icon> <?= lang('App.update') ?>
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
                    <v-btn color="red" dark @click="deleteOpenClose" :loading="loading" elevation="1" large><?= lang('App.delete'); ?></v-btn>
                    <v-spacer></v-spacer>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>
<!-- End Modal Delete -->

<!-- Modal Nota -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalNota" persistent scrollable max-width="350px">
            <v-card class="pa-2">
                <v-card-title class="text-h5">
                    <?= lang('App.report') ?>
                    <v-spacer></v-spacer>
                    <v-btn icon @click="modalNota = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-card-text>
                    <div class="mt-3 mb-3 text-center" style="line-height: normal;">
                        <h3 class="text-display-1 mb-2">
                            LAPORAN TUTUP KASIR<br />
                            TRANSAKSI PENJUALAN
                        </h3>
                    </div>
                    <div class="py-2">
                        Kasir: {{userNama}}<br />
                        Waktu Buka: {{dayjs(tanggalOpen).format('DD-MMM-YYYY')}}, {{dayjs(waktuOpen).format('HH:mm')}}<br />
                        Waktu Tutup: {{dayjs(tanggal).format('DD-MMM-YYYY')}}, {{dayjs(waktu).format('HH:mm')}}<br />
                    </div>
                    <v-divider></v-divider>
                    <div class="py-2">
                        Modal Awal: <span class="float-right">0</span><br />
                    </div>
                    <v-divider></v-divider>
                    <div class="py-2">
                        Cash: <span class="float-right">{{RibuanLocaleNoRp(totalCash)}}</span><br />
                        Transfer Bank: <span class="float-right">{{RibuanLocaleNoRp(totalBank)}}</span><br />
                        Total Penerimaan: <span class="float-right">{{RibuanLocaleNoRp(totalPenerimaan)}}</span><br />
                    </div>
                    <v-divider></v-divider>
                    <div class="py-2">
                        Saldo Akhir: <span class="float-right">{{RibuanLocaleNoRp(totalPenerimaan)}}</span><br />
                    </div>
                    <v-divider></v-divider>
                    <div class="py-2">
                        Transaksi Selesai: <span class="float-right">{{trxSelesai}}</span><br />
                        Transaksi Belum<br />Terbayar: <span class="float-right">{{trxBelumSelesai}}</span><br />
                    </div>
                    <v-divider></v-divider>
                    <div class="py-2">
                        Transaksi Belum<br />Terbayar (Rp): <span class="float-right">{{RibuanLocaleNoRp(totalCredit)}}</span><br />
                    </div>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn large text link :href="'<?= base_url('openclose_cashier/print_html') ?>' + '?date=' + tanggal + '&user=' + userId" target="_blank">
                            <v-icon>mdi-printer</v-icon> <?= lang('App.print') ?>
                        </v-btn>
                    <v-spacer></v-spacer>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>
<!-- End Modal Nota -->

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
        modalAddOpen: false,
        modalAddClose: false,
        modalEdit: false,
        modalDelete: false,
        modalShow: false,
        modalNota: false,
        menu: false,
        startDate: "",
        endDate: "",
        selected: [],
        search: "<?= $search; ?>",
        dataOpenClose: [],
        totalData: 0,
        data: [],
        options: {},
        dataTable: [{
            text: '<?= lang('App.type'); ?>',
            value: 'type'
        }, {
            text: 'Tanggal',
            value: 'tanggal'
        }, {
            text: 'Jam',
            value: 'waktu'
        }, {
            text: 'Shift',
            value: 'id_shift'
        }, {
            text: 'Cashier',
            value: 'email'
        }, {
            text: 'Jml Uang Kertas',
            value: 'jumlah_uang_kertas'
        }, {
            text: 'Jml Uang Koin',
            value: 'jumlah_uang_koin'
        }, {
            text: 'Aksi',
            value: 'actions',
            sortable: false
        }, ],
        dataShift: [],
        shift: [],
        idShift: "",
        id_shiftError: "",
        type: "",
        cashier: "<?= session()->get('email') ?>",
        tanggal: "<?= $hariini; ?>",
        tanggalError: "",
        waktu: "",
        waktuError: "",
        jumlahUangKertas: 0,
        jumlah_uang_kertasError: "",
        jumlahUangKoin: 0,
        jumlah_uang_koinError: "",
        kertas100: 0,
        kertas100Error: "",
        Tkertas100: 0,
        kertas50: 0,
        kertas50Error: "",
        Tkertas50: 0,
        kertas20: 0,
        kertas20Error: "",
        Tkertas20: 0,
        kertas10: 0,
        kertas10Error: "",
        Tkertas10: 0,
        kertas5: 0,
        kertas5Error: "",
        Tkertas5: 0,
        kertas2: 0,
        kertas2Error: "",
        Tkertas2: 0,
        kertas1: 0,
        kertas1Error: "",
        Tkertas1: 0,
        koin1000: 0,
        koin1000Error: "",
        Tkoin1000: 0,
        koin500: 0,
        koin500Error: "",
        Tkoin500: 0,
        koin200: 0,
        koin200Error: "",
        Tkoin200: 0,
        koin100: 0,
        koin100Error: "",
        Tkoin100: 0,
        idShiftOpenClose: "",
        idShiftOpenCloseDetail: "",
        redirect: true,
        tanggalOpen: "",
        waktuOpen: "",
        waktuClose: "",
        dataOpen: [],
        userId: "",
        dataUser: [],
        userNama: "",
        dataReports: [],
        trxSelesai: 0,
        trxBelumSelesai: 0,
        totalCash: 0,
        totalCredit: 0,
        totalBank: 0,
        totalPenerimaan: 0
    }

    // Vue Created
    // Created: Dipanggil secara sinkron setelah instance dibuat
    createdVue = function() {
        this.getOpenClose();
    }

    watchVue = {
        ...watchVue,
        options: {
            handler() {
                this.getDataFromApi()
            },
            deep: true,
        },

        dataOpenClose: function() {
            if (this.dataOpenClose != '') {
                // Call server-side paginate and sort
                this.getDataFromApi();
            }
        },

        status: function() {
            if (this.status == "Absen") {
                this.modalAddOpenShow();
            }
        },

        kertas100: function() {
            this.Tkertas100 = this.kertas100 * 100000;
            this.jumlahUangKertas = this.Tkertas1 + this.Tkertas2 + this.Tkertas5 + this.Tkertas10 + this.Tkertas20 + this.Tkertas50 + this.Tkertas100;
        },
        kertas50: function() {
            this.Tkertas50 = this.kertas50 * 50000;
            this.jumlahUangKertas = this.Tkertas1 + this.Tkertas2 + this.Tkertas5 + this.Tkertas10 + this.Tkertas20 + this.Tkertas50 + this.Tkertas100;
        },
        kertas20: function() {
            this.Tkertas20 = this.kertas20 * 20000;
            this.jumlahUangKertas = this.Tkertas1 + this.Tkertas2 + this.Tkertas5 + this.Tkertas10 + this.Tkertas20 + this.Tkertas50 + this.Tkertas100;
        },
        kertas10: function() {
            this.Tkertas10 = this.kertas10 * 10000;
            this.jumlahUangKertas = this.Tkertas1 + this.Tkertas2 + this.Tkertas5 + this.Tkertas10 + this.Tkertas20 + this.Tkertas50 + this.Tkertas100;
        },
        kertas5: function() {
            this.Tkertas5 = this.kertas5 * 5000;
            this.jumlahUangKertas = this.Tkertas1 + this.Tkertas2 + this.Tkertas5 + this.Tkertas10 + this.Tkertas20 + this.Tkertas50 + this.Tkertas100;
        },
        kertas2: function() {
            this.Tkertas2 = this.kertas2 * 2000;
            this.jumlahUangKertas = this.Tkertas1 + this.Tkertas2 + this.Tkertas5 + this.Tkertas10 + this.Tkertas20 + this.Tkertas50 + this.Tkertas100;
        },
        kertas1: function() {
            this.Tkertas1 = this.kertas1 * 1000;
            this.jumlahUangKertas = this.Tkertas1 + this.Tkertas2 + this.Tkertas5 + this.Tkertas10 + this.Tkertas20 + this.Tkertas50 + this.Tkertas100;
        },

        koin1000: function() {
            this.Tkoin1000 = this.koin1000 * 1000;
            this.jumlahUangKoin = this.Tkoin1000 + this.Tkoin500 + this.Tkoin200 + this.Tkoin100;
        },
        koin500: function() {
            this.Tkoin500 = this.koin500 * 500;
            this.jumlahUangKoin = this.Tkoin1000 + this.Tkoin500 + this.Tkoin200 + this.Tkoin100;
        },
        koin200: function() {
            this.Tkoin200 = this.koin200 * 200;
            this.jumlahUangKoin = this.Tkoin1000 + this.Tkoin500 + this.Tkoin200 + this.Tkoin100;
        },
        koin100: function() {
            this.Tkoin100 = this.koin100 * 100;
            this.jumlahUangKoin = this.Tkoin1000 + this.Tkoin500 + this.Tkoin200 + this.Tkoin100;
        },
    }

    // Vue Methods
    // Methods: Metode-metode yang kemudian digabung ke dalam Vue instance
    methodsVue = {
        ...methodsVue,
        // Format Ribuan Rupiah versi 1
        RibuanLocale(key) {
            const rupiah = 'Rp' + Number(key).toLocaleString('id-ID');
            return rupiah
        },
        RibuanLocaleNoRp(key) {
            const rupiah = Number(key).toLocaleString('id-ID');
            return rupiah
        },

        // Format Ribuan Rupiah versi 2
        Ribuan(key) {
            const format = key.toString().split('').reverse().join('');
            const convert = format.match(/\d{1,3}/g);
            const rupiah = 'Rp' + convert.join('.').split('').reverse().join('');
            return rupiah;
        },
        RibuanNoRp(key) {
            const format = key.toString().split('').reverse().join('');
            const convert = format.match(/\d{1,3}/g);
            const rupiah = convert.join('.').split('').reverse().join('');
            return rupiah;
        },

        toggle(isSelected, select, e) {
            select(!isSelected)
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
                this.getOpenCloseFiltered();
                this.menu = false;
            } else {
                this.getOpenClose();
                this.startDate = "";
                this.endDate = "";
                this.menu = false;
            }
        },

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

                let items = this.dataOpenClose
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

        // Get OpenClose
        getOpenClose: function() {
            this.loading = true;
            axios.get(`<?= base_url() ?>api/openclosecashier`, options)
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        //this.snackbar = true;
                        //this.snackbarMessage = data.message;
                        this.dataOpenClose = data.data;
                        //console.log(this.dataOpenClose);
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.dataOpenClose = data.data;
                        this.data = data.data;
                    }
                    // call method getStatus di backend.php
                    this.getStatus();
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

        // Get OpenClose Filtered
        getOpenCloseFiltered: function() {
            this.loading = true;
            axios.get(`<?= base_url() ?>api/openclosecashier?tgl_start=${this.startDate}&tgl_end=${this.endDate}`, options)
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        //this.snackbar = true;
                        //this.snackbarMessage = data.message;
                        this.dataOpenClose = data.data;
                        //console.log(this.settingData);
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.dataOpenClose = data.data;
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

        // Get Shift
        getShift: function() {
            this.loading1 = true;
            axios.get(`<?= base_url() ?>api/shift/active`, options)
                .then(res => {
                    // handle success
                    this.loading1 = false;
                    var data = res.data;
                    if (data.status == true) {
                        //this.snackbar = true;
                        //this.snackbarMessage = data.message;
                        this.dataShift = data.data;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.dataShift = data.data;
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

        // Get Shift by ID
        getShiftById: function() {
            this.loading1 = true;
            axios.get(`<?= base_url() ?>api/shift/${this.idShift}`, options)
                .then(res => {
                    // handle success
                    this.loading1 = false;
                    var data = res.data;
                    if (data.status == true) {
                        //this.snackbar = true;
                        //this.snackbarMessage = data.message;
                        this.shift = data.data;
                        this.waktuClose = this.shift.jam_selesai;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.shift = data.data;
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

        // Modal Add Open
        modalAddOpenShow: function() {
            this.modalAddOpen = true;
            this.idShift = "";
            this.type = "open";
            this.tanggal = "<?= $hariini; ?>";
            this.waktu = "<?= $jam; ?>";
            this.getShift();
        },
        modalAddOpenClose: function() {
            this.modalAddOpen = false;
            this.$refs.form1.resetValidation();
        },

        // Modal Add Close
        modalAddCloseShow: function() {
            this.modalAddClose = true;
            this.idShift = "";
            this.type = "close";
            this.tanggal = "<?= $hariini; ?>";
            this.waktu = "<?= $jam; ?>";
            this.getShift();
        },
        modalAddCloseClose: function() {
            this.modalAddClose = false;
            this.$refs.form2.resetValidation();
        },

        // Save OpenClose
        saveOpenClose: function() {
            this.loading2 = true;
            axios.post('<?= base_url(); ?>api/openclosecashier/save', {
                    id_shift: this.idShift,
                    type: this.type,
                    tanggal: this.tanggal,
                    waktu: this.waktu,
                    waktuClose: this.waktuClose,
                    jumlah_uang_kertas: this.jumlahUangKertas,
                    jumlah_uang_koin: this.jumlahUangKoin,
                    kertas100: parseInt(this.kertas100),
                    kertas50: parseInt(this.kertas50),
                    kertas20: parseInt(this.kertas20),
                    kertas10: parseInt(this.kertas10),
                    kertas5: parseInt(this.kertas5),
                    kertas2: parseInt(this.kertas2),
                    kertas1: parseInt(this.kertas1),
                    koin1000: parseInt(this.koin1000),
                    koin500: parseInt(this.koin500),
                    koin200: parseInt(this.koin200),
                    koin100: parseInt(this.koin100),
                    redirect: this.redirect
                }, options)
                .then(res => {
                    // handle success
                    this.loading2 = false
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.getOpenClose();
                        this.idShift = "";
                        this.tanggal = "<?= $hariini; ?>";
                        this.waktu = "";
                        this.waktuClose = "";
                        this.jumlah_uang_kertas = 0;
                        this.jumlah_uang_koin = 0;
                        this.kertas100 = 0;
                        this.kertas50 = 0;
                        this.kertas20 = 0;
                        this.kertas10 = 0;
                        this.kertas5 = 0;
                        this.kertas2 = 0;
                        this.kertas1 = 0;
                        this.koin1000 = 0;
                        this.koin500 = 0;
                        this.koin200 = 0;
                        this.koin100 = 0;
                        this.modalAddOpen = false;
                        this.modalAddClose = false;
                        if (this.type == 'open' && this.redirect == true) {
                            setTimeout(() => window.location.href = data.data.url, 3000);
                        }
                        this.$refs.form1.resetValidation();
                        this.$refs.form2.resetValidation();
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
                        this.$refs.form1.resetValidation();
                        this.$refs.form2.resetValidation();
                    }
                })
                .catch(err => {
                    // handle error
                    console.log(err);
                    this.loading2 = false;
                    var error = err.response
                    if (error.data.expired == true) {
                        this.snackbar = true;
                        this.snackbarMessage = error.data.message;
                        setTimeout(() => window.location.href = error.data.data.url, 1000);
                    }
                })
        },

        // Get Item Edit
        editItem: function(item) {
            this.modalEdit = true;
            this.getShift();
            this.idShiftOpenClose = item.id_shift_openclose;
            this.idShift = item.id_shift;
            this.type = item.type;
            this.tanggal = item.tanggal;
            this.waktu = item.waktu;
            this.cashier = item.email;
            this.jumlahUangKertas = item.jumlah_uang_kertas;
            this.jumlahUangKoin = item.jumlah_uang_koin;
            this.getDetailById();
        },
        modalEditClose: function() {
            this.modalEdit = false;
            this.idShift = "";
            this.tanggal = "<?= $hariini; ?>";
            this.waktu = "";
            this.jumlah_uang_kertas = 0;
            this.jumlah_uang_koin = 0;
            this.kertas100 = 0;
            this.kertas50 = 0;
            this.kertas20 = 0;
            this.kertas10 = 0;
            this.kertas5 = 0;
            this.kertas2 = 0;
            this.kertas1 = 0;
            this.koin1000 = 0;
            this.koin500 = 0;
            this.koin200 = 0;
            this.koin100 = 0;
            this.$refs.form.resetValidation();
        },

        // Get Detail by ID
        getDetailById: function() {
            this.loading2 = true;
            axios.get(`<?= base_url() ?>api/openclosecashier/detail/${this.idShiftOpenClose}`, options)
                .then(res => {
                    // handle success
                    this.loading2 = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.idShiftOpenCloseDetail = data.data.id_shift_openclose_detail;
                        this.kertas100 = data.data.kertas100;
                        this.kertas50 = data.data.kertas50;
                        this.kertas20 = data.data.kertas20;
                        this.kertas10 = data.data.kertas10;
                        this.kertas5 = data.data.kertas5;
                        this.kertas2 = data.data.kertas2;
                        this.kertas1 = data.data.kertas1;
                        this.koin1000 = data.data.koin1000;
                        this.koin500 = data.data.koin500;
                        this.koin200 = data.data.koin200;
                        this.koin100 = data.data.koin100;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    // handle error
                    console.log(err);
                    this.loading2 = false;
                    var error = err.response
                    if (error.data.expired == true) {
                        this.snackbar = true;
                        this.snackbarMessage = error.data.message;
                        setTimeout(() => window.location.href = error.data.data.url, 1000);
                    }
                })
        },

        //Update
        updateOpenClose: function() {
            this.loading = true;
            axios.put(`<?= base_url(); ?>api/openclosecashier/update/${this.idShiftOpenClose}`, {
                    id_shift_openclose_detail: this.idShiftOpenCloseDetail,
                    tanggal: this.tanggal,
                    waktu: this.waktu,
                    jumlah_uang_kertas: this.jumlahUangKertas,
                    jumlah_uang_koin: this.jumlahUangKoin,
                    kertas100: parseInt(this.kertas100),
                    kertas50: parseInt(this.kertas50),
                    kertas20: parseInt(this.kertas20),
                    kertas10: parseInt(this.kertas10),
                    kertas5: parseInt(this.kertas5),
                    kertas2: parseInt(this.kertas2),
                    kertas1: parseInt(this.kertas1),
                    koin1000: parseInt(this.koin1000),
                    koin500: parseInt(this.koin500),
                    koin200: parseInt(this.koin200),
                    koin100: parseInt(this.koin100),
                }, options)
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.getOpenClose();
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
                        this.modalEdit = true;
                        this.$refs.form.validate();
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

        // Get Item Delete
        deleteItem: function(item) {
            this.modalDelete = true;
            this.idShiftOpenClose = item.id_shift_openclose;
        },

        // Delete
        deleteOpenClose: function() {
            this.loading = true;
            axios.delete(`<?= base_url() ?>api/openclosecashier/delete/${this.idShiftOpenClose}`, options)
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.getOpenClose();
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

        //Show Nota
        showNota: function(item) {
            this.loading3 = true;
            this.modalNota = true;
            this.tanggal = item.tanggal;
            this.waktu = item.tanggal + ' ' + item.waktu;
            this.userId = item.id_login;
            this.getUser();
            this.getOpen();
            this.getReports();
        },

        // Get User
        getUser: function() {
            this.loading = true;
            axios.get(`<?= base_url(); ?>api/user/${this.userId}`, options)
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        //this.snackbar = true;
                        //this.snackbarMessage = data.message;
                        this.dataUser = data.data;
                        this.userNama = this.dataUser.nama;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.dataUser = data.data;
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

        // Get Open by ID
        getOpen: function() {
            this.loading = true;
            axios.get(`<?= base_url() ?>api/openclosecashier/find/getopen?date=${this.tanggal}&user=${this.userId}`, options)
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        //this.snackbar = true;
                        //this.snackbarMessage = data.message;
                        this.dataOpen = data.data;
                        this.tanggalOpen = this.dataOpen.tanggal;
                        this.waktuOpen = this.dataOpen.tanggal + ' ' + this.dataOpen.waktu;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.dataOpen = data.data;
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

        // Get Reports
        getReports: function() {
            this.loading = true;
            axios.get(`<?= base_url() ?>api/openclosecashier/get/reports?date=${this.tanggal}&user=${this.userId}`, options)
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        //this.snackbar = true;
                        //this.snackbarMessage = data.message;
                        this.dataReports = data.data;
                        this.trxSelesai = this.dataReports.trx_selesai;
                        this.trxBelumSelesai = this.dataReports.trx_belum_selesai;
                        this.totalCash = this.dataReports.total_cash;
                        this.totalCredit = this.dataReports.total_credit;
                        this.totalBank = this.dataReports.total_bank;
                        this.totalPenerimaan = (Number(this.totalCash) + Number(this.totalBank));
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.dataReports = data.data;
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