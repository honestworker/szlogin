<template>
	<div>
        <div class="row page-titles">
            <div class="col-md-12 col-12 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Country</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><router-link to="/home">Home</router-link></li>
                    <li class="breadcrumb-item active">Country</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Add new Country</h4>
                        <country-form @completed="getCountries" :id="id" :idx="idx" :name="name" @interface="cancelCountry"></country-form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Filter Country</h4>
                        <div class="row m-t-40">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Index</label>
                                    <input name="search" class="form-control" v-model="filterCountryForm.idx" @blur="getCountries">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Name</label>
                                    <input class="form-control" v-model="filterCountryForm.name" @blur="getCountries">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Status</label>
                                    <select name="status" class="form-control" v-model="filterCountryForm.status" @change="getCountries">
                                        <option value="">All</option>
                                        <option value="1">Active</option>
                                        <option value="0">Deactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Sort By</label>
                                    <select name="sortBy" class="form-control" v-model="filterCountryForm.sortBy" @change="getCountries">
                                        <option value="idx">Index</option>
                                        <option value="name">Name</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Order</label>
                                    <select name="order" class="form-control" v-model="filterCountryForm.order" @change="getCountries">
                                        <option value="asc">Asc</option>
                                        <option value="desc">Desc</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <h4 class="card-title">Country List</h4>
                        <h6 class="card-subtitle" v-if="countries.total">Total {{countries.total}} result found!</h6>
                        <h6 class="card-subtitle" v-else>No result found!</h6>
                        <div class="table-responsive">
                            <table class="table" v-if="countries.total">
                                <thead>
                                    <tr>
                                        <th>Index</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th style="width:180px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="country in countries.data">
                                        <td v-text="country.idx"></td>
                                        <td v-text="country.name"></td>
                                        <td v-html="getCountryStatus(country)"></td>
                                        <td>
                                            <button class="btn btn-info btn-sm" @click.prevent="editCountry(country)" data-toggle="tooltip" title="Edit Country"><i class="fa fa-pencil"></i></button>
                                            <button v-if="country.status == 1" class="btn btn-danger btn-sm" @click.prevent="toggleCountryStatus(country)" data-toggle="tooltip" title="Mark as Dective"><i class="fa fa-times"></i></button>
                                            <button v-else class="btn btn-success btn-sm" @click.prevent="toggleCountryStatus(country)" data-toggle="tooltip" title="Mark as Active"><i class="fa fa-check"></i></button>
                                            <button class="btn btn-danger btn-sm" @click.prevent="deleteCountry(country)" data-toggle="tooltip" title="Delete Country"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="row">
                                <div class="col-md-8">
                                    <pagination :data="countries" :limit=3 v-on:pagination-change-page="getCountries"></pagination>
                                </div>
                                <div class="col-md-4">
                                    <div class="float-right">
                                        <select name="pageLength" class="form-control" v-model="filterCountryForm.pageLength" @change="getCountries" v-if="countries.total">
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
    </div>
</template>

<script>
    import CountryForm from './form'
    import pagination from 'laravel-vue-pagination'
    import helper from '../../services/helper'
    import ClickConfirm from 'click-confirm'

    export default {
        components : { CountryForm, pagination, ClickConfirm },
        data() {
            return {
                countries: {},
                filterCountryForm: {
                    status: '',
                    sortBy : 'idx',
                    order: 'desc',
                    idx : '',
                    name : '',
                    pageLength: 5
                },
                id: 0,
                idx: '',
                name: '',
            }
        },

        created() {
            this.getCountries();
        },

        methods: {
            getCountries(page) {
                if (typeof page === 'undefined') {
                    page = 1;
                }
                let url = helper.getFilterURL(this.filterCountryForm);
                axios.get('/api/country?page=' + page + url)
                    .then(response => this.countries = response.data);
            },
            deleteCountry(country){
                axios.delete('/api/country/' + country.id).then(response => {
                    toastr['success'](response.data.message);
                    this.getCountries();
                }).catch(error => {
                    if (error.response.data.message) {
                        toastr['error'](error.response.data.message);
                    } else {
                        toastr['error']('The token is expired! Please refresh and try again!');
                    }
                });
            },
            editCountry(country){
                this.id = country.id;
                this.idx = country.idx;
                this.name = country.name;
            },
            cancelCountry(country){
                this.id = 0;
                this.idx = '';
                this.name = '';
            },
            getCountryStatus(country){
                return (country.status == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Deactive</span>';
            },
            toggleCountryStatus(country){
                axios.post('/api/country/status', {id: country.id}).then((response) => {
                    this.getCountries();
                });
            }
        }
    }
</script>
