<template>
    <form @submit.prevent="proceed">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="">Group ID</label>
                    <input class="form-control" type="text" value="" v-model="groupForm.group_id">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="">Organization Number</label>
                    <input class="form-control" type="text" value="" v-model="groupForm.org_number">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="">Organization Name</label>
                    <input class="form-control" type="text" value="" v-model="groupForm.org_name">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="">Contact Person</label>
                    <input class="form-control" type="text" value="" v-model="groupForm.contact_person">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="">Email</label>
                    <input class="form-control" type="text" value="" v-model="groupForm.email">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="">Mobile Number</label>
                    <input class="form-control" type="text" value="" v-model="groupForm.mobile_number">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="">Country</label>
                    <select name="status" class="form-control" v-model="groupForm.country" @change="changeCountry">
                        <option v-for="country in countries.countries" v-bind:value="country" v-bind:selected="getSelectedStatus(country)">{{country}}</option>
                    </select>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-info waves-effect waves-light m-t-10">
            <span v-if="id != 0">Update</span>
            <span v-else>Create</span>
        </button>
        <router-link to="/group" class="btn btn-danger waves-effect waves-light m-t-10" v-show="id">Cancel</router-link>
    </form>
</template>

<script>
    import helper from '../../services/helper'

    export default {
        data() {
            return {
                countries : {},
                groupForm: new Form({
                    'group_id' : '',
                    'org_number' : '',
                    'contact_person' : '',
                    'org_name' : '',
                    'email' : '',
                    'mobile_number' : '',
                    'country' : '',
                }),
            };
        },

        created() {
            this.getCountries();
        },
        props: ['id'],
        mounted() {
            if(this.id != 0)
                this.getGroups();
        },
        methods: {
            sliderChange(value){
                this.groupForm.progress = value;
            },
            proceed(){
                if(this.id != 0)
                    this.updateGroup();
                else
                    this.storeGroup();
            },
            getCountries() {
                axios.post('/api/country/all').then(response => {
                    this.countries = response.data
                }).catch(error => {
                    if (error.response.data) {
                        if (error.response.data.message) {
                            toastr['error'](error.response.data.message);
                        } else {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        }
                    } else {
                        toastr['error']('The token is expired! Please refresh and try again!');
                        this.$router.push('/login');
                    }
                });
            },
            changeCountry(e) {
                if(e.target.options.selectedIndex > -1) {
                    this.groupForm.country = e.target.options[e.target.options.selectedIndex].value;
                }
            },
            getSelectedStatus(country) {
                if (country == this.groupForm.country) {
                    return true;
                } else {
                    return false;
                }
            },
            storeGroup(){
                this.groupForm.post('/api/group')
                .then(response => {
                    toastr['success'](response.message);
                    this.$emit('completed', response.group);
                    this.$router.push('/group');
                }).catch(error => {
                    if (error.response.data) {
                        if (error.response.data.message) {
                            toastr['error'](error.response.data.message);
                        } else {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        }
                    } else {
                        toastr['error']('The token is expired! Please refresh and try again!');
                        this.$router.push('/login');
                    }
                });
            },
            getGroups(){
                axios.get('/api/group/' + this.id)
                .then(response => {
                    this.groupForm.group_id = response.data.group_id;
                    this.groupForm.org_number = response.data.org_number;
                    this.groupForm.contact_person = response.data.contact_person;
                    this.groupForm.org_name = response.data.org_name;
                    this.groupForm.email = response.data.email;
                    this.groupForm.mobile_number = response.data.mobile_number;
                    this.groupForm.country = response.data.country;
                }).catch(error => {
                    if (error.response.data) {
                        if (error.response.data.message) {
                            toastr['error'](error.response.data.message);
                        } else {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        }
                    } else {
                        toastr['error']('The token is expired! Please refresh and try again!');
                        this.$router.push('/login');
                    }
                });
            },
            updateGroup() {
                this.groupForm.patch('/api/group/' + this.id)
                .then(response => {
                    if(response.type == 'error') {
                        if (response.message) {
                            toastr['error'](response.message);
                        } else {
                            toastr['error']('The token is expired! Please refresh and try again!');
                        }
                    }
                    this.$router.push('/group');
                }).catch(error => {
                    if (error.response.data) {
                        if (error.response.data.message) {
                            toastr['error'](error.response.data.message);
                        } else {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        }
                    } else {
                        toastr['error']('The token is expired! Please refresh and try again!');
                        this.$router.push('/login');
                    }
                });
            }
        }
    }
</script>
