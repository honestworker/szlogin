<template>
    <form @submit.prevent="proceed">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="">Group Name</label>
                    <input class="form-control" type="text" value="" v-model="groupForm.name">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="">Postal Code</label>
                    <input class="form-control" type="text" value="" v-model="groupForm.postal_code">
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
                    'name' : '',
                    'postal_code' : '',
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
                axios.get('/admin/countries').then(response => {
                    this.countries = response.data
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
                this.groupForm.post('/admin/group')
                .then(response => {
                    toastr['success'](response.message);
                    this.$emit('completed', response.group);
                    this.$router.push('/group');
                }).catch(error => {
                    if (error.status == 'fail') {
                        if (error.type == "token_error") {
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
            getGroups(){
                axios.get('/admin/group/' + this.id)
                .then(response => {
                    this.groupForm.name = response.data.name;
                    this.groupForm.postal_code = response.data.postal_code;
                    this.groupForm.country = response.data.country;
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
            updateGroup() {
                this.groupForm.patch('/admin/group/' + this.id)
                .then(response => {
                    if (response.message) {
                        toastr['success'](response.message);
                        console.log(response.data);
                    }
                    this.$router.push('/group');
                }).catch(error => {
                    if (error.status == 'fail') {
                        if (error.type == "token_error") {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        } else {
                            toastr['error'](error.message);
                        }
                    } else {
                        if (error.message) {
                            toastr['error']('An unexpected error occurred!');
                            console.log(error.message);
                        }
                    }
                });
            }
        }
    }
</script>
