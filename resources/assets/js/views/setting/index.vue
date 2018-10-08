<template>
	<div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Setting</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/dashboard">Dashboard</router-link></li>
                    <li class="breadcrumb-item active">Setting</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="saveConfiguration">
                            <div class="form-group">
                                <label for="">Company Name</label>
                                <input class="form-control" type="text" value="" v-model="settingForm.company_name">
                            </div>
                            <div class="form-group">
                                <label for="">Contact Person</label>
                                <input class="form-control" type="text" value="" v-model="settingForm.contact_person">
                            </div>
                            <button type="submit" class="btn btn-info waves-effect waves-light m-t-10">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import helper from '../../services/helper'

    export default {
        data() {
            return {
                settingForm: new Form({
                    company_name : '',
                    contact_person: '',
                }, false)
            };
        },
        mounted(){
            axios.get('/api/setting/fetch').then(response => {
                this.settingForm = helper.formAssign(this.settingForm, response.data.config);
            }).catch();
        },
        components : { },
        methods: {
            saveSetting(){
                this.settingForm.post('/api/setting').then(response => {
                    this.$store.dispatch('setConfig',this.settingForm);
                    toastr['success'](response.message);
                }).catch(response => {
                    toastr['error'](response.message);
                });
            }
        }
    }
</script>
