<v-data-table :headers="thStokOpname" :items="dataStokopnameWithIndex" :items-per-page="-1" class="elevation-1" :loading="loading">
    <template v-slot:item="{ item }">
        <tr>
            <td>
                <a :href="'<?= base_url('barang'); ?>?search=' + item.kode_barang" target="_blank" title="item.kode_barang" alt="item.kode_barang">{{item.kode_barang}}</a>
            </td>
            <td>{{item.barcode}}</td>
            <td>{{item.nama_barang}}</td>
            <td>{{item.stok}}</td>
            <td>{{item.stok_nyata}}</td>
            <td>{{item.selisih}}</td>
            <td>{{RibuanLocale(item.nilai)}}</td>
            <td>{{item.keterangan}}</td>
            <td>{{dayjs(item.created_at).format('DD-MM-YYYY HH:mm')}}</td>
        </tr>
    </template>
    <template v-slot:footer.prepend>
        <v-btn outlined :href="'<?= base_url('laporan/stokopname-pdf') ?>' + '?tgl_start=' + startDate + '&tgl_end=' + endDate" target="_blank" v-show="dataStokOpname != ''">
            <v-icon>mdi-download</v-icon> PDF
        </v-btn>
    </template>
</v-data-table>