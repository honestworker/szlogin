<template>
	<div>
        <div class="row page-titles">
            <div class="col-md-12 col-12 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Advertisement</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/dashboard">Dashboard</router-link></li>
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
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Name</label>
                                    <input name="search" class="form-control" v-model="filterAdForm.name" @change="getAds">
                                </div>
                            </div>
                            <div class="col-md-3">
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
                                                <select name="status" class="form-control col-md-4" v-model="filterAdForm.show_count_oper" @change="changeShowCountOper">
                                                    <option value="=" selected="true">=</option>
                                                    <option value="!=">!=</option>
                                                    <option value=">">></option>
                                                    <option value=">=">>=</option>
                                                    <option value="<"><</option>
                                                    <option value="<="><=</option>
                                                </select>
                                                <input class="form-control col-md-7" v-model="filterAdForm.show_count" @change="getAds">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Click Count</label>
                                            <div class="row">
                                                <select name="status" class="form-control col-md-4" v-model="filterAdForm.click_count_oper" @change="changeClickCountOper">
                                                    <option value="=" selected="true">=</option>
                                                    <option value="!=">!=</option>
                                                    <option value=">">></option>
                                                    <option value=">=">>=</option>
                                                    <option value="<"><</option>
                                                    <option value="<="><=</option>
                                                </select>
                                                <input class="form-control col-md-7" v-model="filterAdForm.click_count" @change="getAds">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Start Date</label>
                                            <div class="row">
                                                <select name="status" class="form-control col-md-3" v-model="filterAdForm.start_date_oper" @change="changeStartDateOper">
                                                    <option value="=" selected="true">=</option>
                                                    <option value="!=">!=</option>
                                                    <option value=">">></option>
                                                    <option value=">=">>=</option>
                                                    <option value="<"><</option>
                                                    <option value="<="><=</option>
                                                </select>
                                                <datepicker class="col-md-7" v-model="filterAdForm.start_date" :bootstrapStyling="true" @change="getAds"></datepicker>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">End Date</label>
                                            <div class="row">
                                                <select name="status" class="form-control col-md-3" v-model="filterAdForm.end_date_oper" @change="changeEndDateOper">
                                                    <option value="=" selected="true">=</option>
                                                    <option value="!=">!=</option>
                                                    <option value=">">></option>
                                                    <option value=">=">>=</option>
                                                    <option value="<"><</option>
                                                    <option value="<="><=</option>
                                                </select>
                                                <datepicker class="col-md-7" v-model="filterAdForm.end_date" :bootstrapStyling="true" @change="getAds"></datepicker>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Postal Code Min</label>
                                    <input name="min_postal" class="form-control" v-model="filterAdForm.min_postal" @change="getAds">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Postal Code Max</label>
                                    <input name="max_postal" class="form-control" v-model="filterAdForm.max_postal" @change="getAds">
                                </div>
                            </div>
                            <div class="col-md-3">
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
                        <button type="submit" class="btn btn-info waves-effect waves-light m-t-10" @click="modalExportPDF" style="float: right; text-align: right;">
                            <span>Export</span>
                        </button>
                        <div class="table-responsive">
                            <table class="table" v-if="ads.total">
                                <thead>
                                    <tr>
                                        <th><input type='checkbox' v-model='isCheckAll' @click="selectAll"></th>
                                        <th>Name</th>
                                        <th>Photo</th>
                                        <th>Country</th>
                                        <th>Link URL</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Show Count</th>
                                        <th>Unique Show Count</th>
                                        <th>Click Count</th>
                                        <th>Unique Click Count</th>
                                        <th>Status</th>
                                        <th style="width:180px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="ad in ads.data">
                                        <td><input type="checkbox" :value="ad.id" id="ad_check" v-model="exportAdForm.selectedAds"></td>
                                        <td v-text="ad.name"></td>
                                        <td><img :src="getImageUrl(ad.image)" class="img-responsive img-max-width-200"></td>
                                        <td v-text="ad.country"></td>
                                        <td v-text="ad.link"></td>
                                        <td v-text="ad.start_date"></td>
                                        <td v-text="ad.end_date"></td>
                                        <td v-text="numberToString(ad.show_sum)"></td>
                                        <td v-text="numberToString(ad.show_count)"></td>
                                        <td v-text="numberToString(ad.click_sum)"></td>
                                        <td v-text="numberToString(ad.click_count)"></td>
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
        
        <!-- Export Advertisement Modal -->
        <div class="modal" id="modal-export-ad" tabindex="-1" role="dialog">
            <div class="modal-dialog" v-if="exportAd">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Export Advertisement
                        </h5>
                    </div>

                    <div class="modal-body">
                        <input type="checkbox" :value="exportAdForm.show_month" v-model="exportAdForm.show_month">Total number of app visitors last 30 days
                        <br>
                        <input type="checkbox" :value="exportAdForm.show_month2" v-model="exportAdForm.show_month2">Total number of app visitors last 60 days
                        <br>
                        <input type="checkbox" :value="exportAdForm.show_month3" v-model="exportAdForm.show_month3">Total number of app visitors last 90 days
                        <br>
                        <input type="checkbox" :value="exportAdForm.show_lifetime" v-model="exportAdForm.show_lifetime">Total number of app visitors during publishing time
                        <br>
                        <input type="checkbox" :value="exportAdForm.show_year" v-model="exportAdForm.show_year">Total number of app visitors this year
                        <br>
                        <input type="checkbox" :value="exportAdForm.show_all" v-model="exportAdForm.show_all">Total number of app visitors in total (since start of the app)
                        <br>
                        <input type="checkbox" :value="exportAdForm.click_month" v-model="exportAdForm.click_month">Total number of clicks on banner last 30 days
                        <br>
                        <input type="checkbox" :value="exportAdForm.click_month2" v-model="exportAdForm.click_month2">Total number of clicks on banner last 60 days
                        <br>
                        <input type="checkbox" :value="exportAdForm.click_month3" v-model="exportAdForm.click_month3">Total number of clicks on banner last 90 days
                        <br>
                        <input type="checkbox" :value="exportAdForm.click_lifetime" v-model="exportAdForm.click_lifetime">Total number of clicks on banner during publishing time
                        <br>
                        <input type="checkbox" :value="exportAdForm.click_year" v-model="exportAdForm.click_year">Total number of clicks on banner this year
                        <br>
                        <input type="checkbox" :value="exportAdForm.click_unique" v-model="exportAdForm.click_unique">Total number of clicks on banner by unique visitors during publishing time
                        <br>
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">No, Go Back</button>
                        <button type="button" class="btn btn-primary" @click.prevent="exportPDF">
                            Export Advertisements
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
    import XLSX from 'xlsx'

    export default {
        components : { datepicker, pagination, ClickConfirm },
        data() {
            return {
                ads: {},
                countries : {},
                filterAdForm: {
                    status: '',
                    name: '',
                    image : '',
                    country : '',
                    link : '',
                    show_count : '',
                    click_count : '',
                    start_date : '',
                    start_date_oper : '',
                    end_date : '',
                    end_date_oper : '',
                    min_postal : '',
                    max_postal : '',
                    pageLength: 100
                },
                ad_id : 0,
                deletingAd : 1,
                exportAd : 1,

                exportAdForm: {
                    selectedAds : [],
                    show_month: 1,
                    show_month2: 1,
                    show_month3: 1,
                    show_lifetime: 1,
                    show_year: 1,
                    show_all: 1,
                    click_month: 1,
                    click_month2: 1,
                    click_month3: 1,
                    click_lifetime: 1,
                    click_year: 1,
                    click_unique: 1,
                },
                isCheckAll : false,

                baseUrl : '',
            }
        },

        created() {
            this.getAds();
            this.getCountries();
        },

        methods: {
            processDataTimes() {
                if (this.filterAdForm.start_date) {
                    this.filterAdForm.start_date = moment(this.filterAdForm.start_date).format('YYYY-MM-DD');
                } else {
                    this.filterAdForm.start_date = '';
                }
                if (this.filterAdForm.end_date) {
                    this.filterAdForm.end_date = moment(this.filterAdForm.end_date).format('YYYY-MM-DD');
                } else {
                    this.filterAdForm.end_date = '';
                }
            },
            getAds(page) {
                this.baseUrl = window.location.origin;
                if (typeof page === 'undefined') {
                    page = 1;
                }
                this.processDataTimes();
                let url = helper.getFilterURL(this.filterAdForm);
                axios.get('/admin/advertisement?page=' + page + url).then(response => {
                    this.ads = response.data;
                }).catch(error => {
                    if (error.response.data.status == 'fail') {
                        if (error.response.data.type == "token_error") {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        } else {
                            toastr['error'](error.response.data.message);
                        }
                    } else {
                        if (error.message) {
                            toastr['error']('An unexpected error occurred!');
                            console.log(error.message);
                        }
                    }
                });
            },
            getCountries() {
                axios.get('/admin/countries').then(response => {
                    this.countries = response.data;
                }).catch(error => {
                    if (error.response.data.status == 'fail') {
                        if (error.response.data.type == "token_error") {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        } else {
                            toastr['error'](error.response.data.message);
                        }
                    } else {
                        if (error.message) {
                            toastr['error']('An unexpected error occurred!');
                            console.log(error.message);
                        }
                    }
                });
            },
            modalDeleteAd(ad) {
                this.ad_id = ad.id;
                $('#modal-delete-ad').modal('show');
            },
            deleteAd() {
                axios.delete('/admin/advertisement/' + this.ad_id).then(response => {
                    $('#modal-delete-ad').modal('hide');
                    toastr['success'](response.data.message);
                    this.getAds();
                }).catch(error => {
                    if (error.response.data.status == 'fail') {
                        if (error.response.data.type == "token_error") {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        } else {
                            toastr['error'](error.response.data.message);
                        }
                    } else {
                        if (error.message) {
                            toastr['error']('An unexpected error occurred!');
                            console.log(error.message);
                        }
                    }
                    $('#modal-delete-ad').modal('hide');
                });
            },
            viewAd(ad){
                this.$router.push('/advertisement/' + ad.id);
            },
            getAdStatus(ad){
                return (ad.status == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Deactive</span>';
            },
            toggleAdStatus(ad){
                axios.patch('/admin/advertisement/status', {id: ad.id}).then((response) => {
                    this.getAds();
                }).catch(error => {
                    if (error.response.data.status == 'fail') {
                        if (error.response.data.type == "token_error") {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        } else {
                            toastr['error'](error.response.data.message);
                        }
                    } else {
                        if (error.message) {
                            toastr['error']('An unexpected error occurred!');
                            console.log(error.message);
                        }
                    }
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
                    return this.baseUrl + '/images/advertisements/' + url;
                } else {
                    return this.baseUrl + '/images/common/no-image.png';
                }                
            },
            changeShowCountOper(e) {
                if(e.target.options.selectedIndex > -1) {
                    this.filterAdForm.show_count_oper = e.target.options[e.target.options.selectedIndex].value;
                    this.getAds();
                }
            },
            changeClickCountOper(e) {
                if(e.target.options.selectedIndex > -1) {
                    this.filterAdForm.click_count_oper = e.target.options[e.target.options.selectedIndex].value;
                    this.getAds();
                }
            },
            changeStartDateOper(e) {
                if(e.target.options.selectedIndex > -1) {
                    this.filterAdForm.start_date_oper = e.target.options[e.target.options.selectedIndex].value;
                    this.getAds();
                }
            },
            changeEndDateOper(e) {
                if(e.target.options.selectedIndex > -1) {
                    this.filterAdForm.end_date_oper = e.target.options[e.target.options.selectedIndex].value;
                    this.getAds();
                }
            },
            modalExportPDF() {
                $('#modal-export-ad').modal('show');
            },
            exportPDF() {
                let url = helper.getFilterURL(this.exportAdForm);
                axios.patch('/admin/advertisement/infor?' + url).then(response => {
                    var exportedPDFdata = response.data.data;

                    // var columns = [];
                    // columns.push({title: 'Name', dataKey: 'name'});
                    // columns.push({title: 'Link', dataKey: 'link'});
                    // if (this.exportAdForm.show_month)
                    //     columns.push({title: 'Total number\n of app visitors\n last 30 days', dataKey: 'show_month'});
                    // if (this.exportAdForm.show_month2)
                    //     columns.push({title: 'Total number\n of app visitors\n last 60 days', dataKey: 'show_month2'});
                    // if (this.exportAdForm.show_month3)
                    //     columns.push({title: 'Total number\n of app visitors\n last 90 days', dataKey: 'show_month3'});
                    // if (this.exportAdForm.show_lifetime)
                    //     columns.push({title: 'Total number\n of app visitors\n during publishing time', dataKey: 'show_lifetime'});
                    // if (this.exportAdForm.show_year)
                    //     columns.push({title: 'Total number\n of app visitors\n this year', dataKey: 'show_year'});
                    // if (this.exportAdForm.show_all)
                    //     columns.push({title: 'Total number\n of app visitors\n in total \n(since start of the app)', dataKey: 'show_all'});

                    // if (this.exportAdForm.click_month)
                    //     columns.push({title: 'Total number\n of clicks on banner\n last 30 days', dataKey: 'click_month'});
                    // if (this.exportAdForm.click_month2)
                    //     columns.push({title: 'Total number\n of clicks on banner\n last 60 days', dataKey: 'click_month2'});
                    // if (this.exportAdForm.click_month3)
                    //     columns.push({title: 'Total number\n of clicks on banner\n last 90 days', dataKey: 'click_month3'});
                    // if (this.exportAdForm.click_lifetime)
                    //     columns.push({title: 'Total number\n of clicks on banner\n during publishing time', dataKey: 'click_lifetime'});
                    // if (this.exportAdForm.click_year)
                    //     columns.push({title: 'Total number\n of clicks on banner\n this year', dataKey: 'click_year'});
                    // if (this.exportAdForm.click_unique)
                    //     columns.push({title: 'Total number\n of clicks on banner\n by unique visitors\n during publishing time', dataKey: 'click_unique'});

                    // let pdfName = 'report.pdf';
                    // var doc = new jsPDF('p', 'pt');
                    // //doc.text("Hello World", 10, 10);
                    // doc.autoTable(columns, exportedPDFdata);
                    // doc.save(pdfName);
                    
                    var columns = exportedPDFdata;
                    var exportSheet = XLSX.utils.json_to_sheet(exportedPDFdata);

                    var wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, exportSheet, 'report');
                    XLSX.writeFile(wb, 'report.xlsx');

                    $('#modal-export-ad').modal('hide');
                    toastr['success'](response.data.message);
                }).catch(error => {
                    if (error.response.data.status == 'fail') {
                        if (error.response.data.type == "token_error") {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        } else {
                            toastr['error'](error.response.data.message);
                        }
                    } else {
                        if (error.message) {
                            toastr['error']('An unexpected error occurred!');
                            console.log(error.message);
                        }
                    }
                    $('#modal-export-ad').modal('hide');
                });
            },
            selectAll() {
                var selected = [];

                if (!this.isCheckAll) {
                    this.ads.data.forEach(function (ad) {
                        selected.push(ad.id);
                    });
                }
                
                this.exportAdForm.selectedAds = selected;
            }
        },
    }
</script>
