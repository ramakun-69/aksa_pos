<v-data-table :headers="thPenjualan" :items="dataPenjualanWithIndex" :items-per-page="-1" class="elevation-1" :loading="loading">
    <template v-slot:item="{ item }">
        <tr>
            <td>{{item.index}}</td>
            <td><a :href="'<?= base_url('penjualan'); ?>?faktur=' + item.faktur" title="" alt="">{{item.faktur}}</a></td>
            <td>{{dayjs(item.created_at).format('DD-MM-YYYY HH:mm')}}</td>
            <td>{{item.nama_kontak}}</td>
            <td>{{item.jumlah}}</td>
            <td>{{Ribuan(item.subtotal)}}</td>
            <td>{{Ribuan(item.diskon)}}</td>
            <td>{{Ribuan(item.pajak)}}</td>
            <td>{{RibuanLocale(item.pembulatan)}}</td>
            <td>{{Ribuan(item.total)}}</td>
            <td>{{RibuanLocale(item.total_laba)}}</td>
            <td>
                <div v-if="item.id_piutang == null">
                    <v-chip color="success" small label>Paid</v-chip>
                </div>
                <div v-else>
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on, attrs }">
                            <span v-bind="attrs" v-on="on" v-if="item.status_piutang == 1">
                                <v-chip color="success" small label>Paid</v-chip>
                            </span>
                            <span v-bind="attrs" v-on="on" v-else>
                                <v-chip color="error" small label><a class="white--text" :href="'<?= base_url('piutang'); ?>?faktur=' + item.faktur">Unpaid</a></v-chip>
                            </span>
                        </template>
                        <span>
                            Jml. Bayar: {{Ribuan(item.bayar)}}<br />
                            Sisa Piutang: {{Ribuan(item.sisa_piutang)}}<br />
                            Keterangan: {{item.keterangan ?? "-"}}
                        </span>
                    </v-tooltip>
                </div>
            </td>
            <td>{{item.nama}}</td>
        </tr>
    </template>
    <template slot="body.append">
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td class="text-right">Total</td>
            <td>{{ sumTotalPenjualan('jumlah') }}</td>
            <td>{{ Ribuan(sumTotalPenjualan('subtotal')) }}</td>
            <td></td>
            <td>{{ Ribuan(sumTotalPenjualan('pajak')) }}</td>
            <td>{{ RibuanLocale(sumTotalPenjualan('pembulatan')) }}</td>
            <td>{{ Ribuan(sumTotalPenjualan('total')) }}</td>
            <td>{{ RibuanLocale(sumTotalPenjualan('total_laba')) }}</td>
            <td>- {{ Ribuan(sumTotalPenjualan('sisa_piutang')) }}</td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="3" class="text-right">*Saldo Kas =<br />(Total-Piutang-Pajak)</td>
            <td><strong>{{ RibuanLocale(sumTotalPenjualan('total')-sumTotalPenjualan('sisa_piutang')-sumTotalPenjualan('pajak')) }}</strong></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </template>
    <template v-slot:footer.prepend>
        <v-btn outlined :href="'<?= base_url('laporan/penjualan-pdf') ?>' + '?tgl_start=' + startDate + '&tgl_end=' + endDate" target="_blank" v-show="dataPenjualan != ''">
            <v-icon>mdi-download</v-icon> PDF
        </v-btn>
    </template>
</v-data-table>