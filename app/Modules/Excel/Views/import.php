<?php $this->extend("layouts/backend"); ?>
<?php $this->section("content"); ?>
<template>
    <v-card>
        <v-card-title>
            <h2 class="mb-2 font-weight-medium"><?= $title; ?></h2>
            <v-btn color="success" outlined class="ms-3" link href="<?= base_url('files/Format_Import_Excel.xlsx'); ?>" elevation="1">
                <v-icon>mdi-download</v-icon> Download Format
            </v-btn>
        </v-card-title>
        <v-card-text>
            <h2 class="mb-3">File Upload</h2>
            <template>
                <?php
                if (session()->getFlashdata('error')) {
                ?>
                    <v-alert text outlined color="deep-orange" icon="mdi-alert-octagon" dense>
                        <?= session()->getFlashdata('error') ?>
                    </v-alert>
                <?php } ?>

                <?php if (session()->getFlashdata('success')) { ?>
                    <v-alert text outlined outlined type="success" dense>
                        <?= session()->getFlashdata('success') ?>
                    </v-alert>
                <?php } ?>
                <form method="post" action="<?= base_url('excel/saveExcel'); ?>" enctype="multipart/form-data">
                    <v-checkbox name="ignorename" v-model="checkbox" label="Ignore the same Item Name" :value="checkbox"></v-checkbox>

                    <v-file-input show-size label="Upload File anda disini" id="file" name="fileexcel" class="mb-2" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" filled error-messages="<?= validation_show_error('fileexcel', $template = 'text') ?>"></v-file-input>
                    <v-btn large type="submit" color="primary" elevation="1">
                        <v-icon>mdi-upload</v-icon> Upload
                    </v-btn>
                </form>

            </template>
        </v-card-text>
        <v-card-actions>

        </v-card-actions>
    </v-card>
</template>
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

    // Initial Data
    dataVue = {
        ...dataVue,
        checkbox: true,

    }

    // Vue Created
    createdVue = function() {

    }

    // Vue Methods
    methodsVue = {
        ...methodsVue,

    }
</script>
<?php $this->endSection("js") ?>