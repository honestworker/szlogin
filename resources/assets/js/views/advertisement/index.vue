<template>
	<div>
        <div class="row page-titles">
            <div class="col-md-12 col-12 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Advertisement</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">Home</router-link></li>
                    <li class="breadcrumb-item active">Advertisement</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 col-12">
                <div class="card">
                    <div class="card-body">
                        <router-link to="/advertisement/0" class="btn btn-success waves-effect waves-light m-t-10">Create Advertisement</router-link>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Filter Advertisement</h4>
                        <div class="row m-t-20">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Country</label>
                                    <select name="status" class="form-control" v-model="filterAdForm.country" @change="getAds">
                                        <option value="">All</option>
                                        <option v-for="country in countries.countries" v-bind:value="country">{{country}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Show Count</label>
                                            <div class="row">
                                                <select name="status" class="form-control col-md-4" v-model="filterAdForm.show_count_oper" @change="getAds">
                                                    <option value="0" selected>=</option>
                                                    <option value="1">!=</option>
                                                    <option value="2">></option>
                                                    <option value="3">>=</option>
                                                    <option value="4"><</option>
                                                    <option value="5"><=</option>
                                                </select>
                                                <input class="form-control col-md-7" v-model="filterAdForm.show_count" @blur="getAds">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">                            
                                        <div class="form-group">
                                            <label for="">Link Count</label>
                                            <div class="row">
                                                <select name="status" class="form-control col-md-4" v-model="filterAdForm.link_count_oper" @change="getAds">
                                                    <option value="0" selected>=</option>
                                                    <option value="1">!=</option>
                                                    <option value="2">></option>
                                                    <option value="3">>=</option>
                                                    <option value="4"><</option>
                                                    <option value="5"><=</option>
                                                </select>
                                                <input class="form-control col-md-7" v-model="filterAdForm.link_count" @blur="getAds">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Start Date</label>
                                            <div class="row">
                                                <select name="status" class="form-control col-md-4" v-model="filterAdForm.start_date_oper" @change="getAds">
                                                    <option value="0" selected>=</option>
                                                    <option value="1">!=</option>
                                                    <option value="2">></option>
                                                    <option value="3">>=</option>
                                                    <option value="4"><</option>
                                                    <option value="5"><=</option>
                                                </select>
                                                <datepicker class="col-md-7" v-model="filterAdForm.start_date" :bootstrapStyling="true" @blur="getAds"></datepicker>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">End Date</label>
                                            <div class="row">
                                                <select name="status" class="form-control col-md-4" v-model="filterAdForm.end_date_oper" @change="getAds">
                                                    <option value="0" selected>=</option>
                                                    <option value="1">!=</option>
                                                    <option value="2">></option>
                                                    <option value="3">>=</option>
                                                    <option value="4"><</option>
                                                    <option value="5"><=</option>
                                                </select>
                                                <datepicker class="col-md-7" v-model="filterAdForm.end_date" :bootstrapStyling="true" @blur="getAds"></datepicker>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Status</label>
                                    <select name="status" class="form-control" v-model="filterAdForm.status" @change="getAds">
                                        <option value="">All</option>
                                        <option value="1">Active</option>
                                        <option value="0">Deactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <h4 class="card-title">Advertisement List</h4>
                        <h6 class="card-subtitle" v-if="ads.total">Total {{ads.total}} result found!</h6>
                        <h6 class="card-subtitle" v-else>No result found!</h6>
                        <div class="table-responsive">
                            <table class="table" v-if="ads.total">
                                <thead>
                                    <tr>
                                        <th>Photo</th>
                                        <th>Country</th>
                                        <th>Link URL</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Show Count</th>
                                        <th>Link Count</th>
                                        <th>Status</th>
                                        <th style="width:180px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="ad in ads.data">
                                        <td><img :src="getImageUrl(ad.image)" class="img-responsive" style="max-width: 200px;"></td>
                                        <td v-text="ad.country"></td>
                                        <td v-text="ad.link"></td>
                                        <td v-text="ad.start_date"></td>
                                        <td v-text="ad.end_date"></td>
                                        <td v-text="numberToString(ad.show_count)"></td>
                                        <td v-text="numberToString(ad.link_count)"></td>
                                        <td v-html="getAdStatus(ad)"></td>
                                        <td>
                                            <button class="btn btn-info btn-sm" @click.prevent="viewAd(ad)" data-toggle="tooltip" title="Edit Advertisement"><i class="fa fa-pencil"></i></button>
                                            <button v-if="ad.status == 1" class="btn btn-danger btn-sm" @click.prevent="toggleAdStatus(ad)" data-toggle="tooltip" title="Mark as Dective"><i class="fa fa-times"></i></button>
                                            <button v-else class="btn btn-success btn-sm" @click.prevent="toggleAdStatus(ad)" data-toggle="tooltip" title="Mark as Active"><i class="fa fa-check"></i></button>
                                            <button class="btn btn-danger btn-sm" @click.prevent="modalDeleteAd(ad)" data-toggle="tooltip" title="Delete Advertisement"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="row">
                                <div class="col-md-8">
                                    <pagination :data="ads" :limit=3 v-on:pagination-change-page="getAds"></pagination>
                                </div>
                                <div class="col-md-4">
                                    <div class="float-right">
                                        <select name="pageLength" class="form-control" v-model="filterAdForm.pageLength" @change="getAds" v-if="ads.total">
                                            <option value="5">5 per page</option>
                                            <option value="10">10 per page</option>
                                            <option value="25">25 per page</option>
                                            <option value="100">100 per page</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Delete Advertisement Modal -->
        <div class="modal" id="modal-delete-ad" tabindex="-1" role="dialog">
            <div class="modal-dialog" v-if="deletingAd">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Delete Advertisement
                        </h5>
                    </div>

                    <div class="modal-body">
                        Are you sure you want to delete this Advertisement?
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">No, Go Back</button>
                        <button type="button" class="btn btn-danger" @click.prevent="deleteAd()">
                            Yes, Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import datepicker from 'vuejs-datepicker'
    import pagination from 'laravel-vue-pagination'
    import helper from '../../services/helper'
    import ClickConfirm from 'click-confirm'

    export default {
        components : { datepicker, pagination, ClickConfirm },
        data() {
            return {
                ads: {},
                countries : {},
                filterAdForm: {
                    status: '',
                    image : '',
                    country : '',
                    link : '',
                    show_count : '',
                    link_count : '',
                    start_date : '',
                    start_date_oper : '',
                    end_date : '',
                    end_date_oper : '',
                    pageLength: 5
                },
                ad_id : 0,
                deletingAd : 1,
            }
        },

        created() {
            this.getAds();
            this.getCountries();
        },

        methods: {
            getAds(page) {
                if (typeof page === 'undefined') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterAdForm);
                axios.get('/api/advertisement?page=' + page + url)
                    .then(response => this.ads = response.data);
            },
            getCountries() {
                axios.post('/api/country/all')
                    .then(response => this.countries = response.data);
            },
            modalDeleteAd(ad) {
                this.ad_id = ad.id;
                $('#modal-delete-ad').modal('show');
            },
            deleteAd() {
                axios.delete('/api/advertisement/' + this.ad_id).then(response => {
                    toastr['success'](response.data.message);
                    $('#modal-delete-ad').modal('hide');
                    this.getUsers();
                }).catch(error => {
                    if (error.response.data.message) {
                        toastr['error'](error.response.data.message);
                    } else {
                        toastr['error']('The token is expired! Please refresh and try again!');
                    }
                });
                this.getAds();
            },
            viewAd(ad){
                this.$router.push('/advertisement/' + ad.id);
            },
            getAdStatus(ad){
                return (ad.status == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Deactive</span>';
            },
            toggleAdStatus(ad){
                axios.post('/api/ad/status', {id: ad.id}).then((response) => {
                    this.getAds();
                });
            },
            numberToString(number) {
                if (number) {
                    return number;
                } else {
                    return "0";
                }
            },
            getImageUrl(url) {
                if (url) {
                    return 'http://szlogin.com/images/advertisements/' + url;
                } else {
                    return 'http://szlogin.com/images/advertisements/no-image.png';
                }
                
            }
        }
    }
</script>
