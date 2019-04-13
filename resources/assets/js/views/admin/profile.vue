<template>
    <form @submit.prevent="proceed">
        <div class="view-user-profile">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <strong><p>Avatar: </p></strong>
                                <div class="profile-img"> <img :src="getAvatar" alt="user" /></div>
                                <h4 class="card-title">Upload Avatar</h4>
                                <div class="form-group text-center m-t-20">
                                    <span id="fileselector">
                                        <label class="btn btn-info">
                                            <input type="file"  @change="uploadAvatar" id="avatarUpload" class="upload-button">
                                            <i class="fa fa-upload margin-correction"></i>Choose Avatar
                                        </label>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-5 text-right">
                                        <strong><span>Email: </span></strong>
                                    </div>
                                    <div class="col-lg-7">
                                        <span v-text="adminForm.email"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-5 text-right">
                                        <strong><span>Created At: </span></strong>
                                    </div>
                                    <div class="col-lg-7">
                                        <span v-text="adminForm.created_at"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <h4 class="card-title">Change Password</h4>
                                <div class="form-group">
                                    <label for="">Current Password</label>
                                    <input class="form-control" type="password" value="" v-model="passwordForm.current_password">
                                </div>
                                <div class="form-group">
                                    <label for="">New Password</label>
                                    <input class="form-control" type="password" value="" v-model="passwordForm.new_password">
                                </div>
                                <div class="form-group">
                                    <label for="">Confirm Password</label>
                                    <input class="form-control" type="password" value="" v-model="passwordForm.new_password_confirmation">
                                </div>
                                <button type="submit" class="btn btn-info waves-effect waves-light m-t-10" @click="changePassword">Change Password</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-5 text-right">
                                        <strong><span>Full Name: </span></strong>
                                    </div>
                                    <div class="col-lg-7">
                                        <input class="form-control" type="text" value="" v-model="adminForm.full_name">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-5 text-right">
                                        <strong><span>Street Address: </span></strong>
                                    </div>
                                    <div class="col-lg-7">
                                        <input class="form-control" type="text" value="" v-model="adminForm.street_address">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-5 text-right">
                                        <strong><span>Postal Code: </span></strong>
                                    </div>
                                    <div class="col-lg-7">
                                        <input class="form-control" type="text" value="" v-model="adminForm.postal_code">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-5 text-right">
                                        <strong><span>Country: </span></strong>
                                    </div>
                                    <div class="col-lg-7">
                                        <select name="status" class="form-control" v-model="adminForm.country">
                                            <option v-for="country in countries.countries" v-bind:value="country" v-bind:selected="getCountrySelectedStatus(country)">{{country}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-info waves-effect waves-light m-t-10" @click="updateProfile"><span>Save</span></button>
                    <router-link to="/dashboard" class="btn btn-danger waves-effect waves-light m-t-10">Cancel</router-link>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
    import helper from '../../services/helper'

    export default {
        data() {
            return {
                countries : {},
                groups : {},
                adminForm : {},
                passwordForm: new Form({
                    'current_password' : '',
                    'new_password' : '',
                    'new_password_confirmation' : '',
                }),
                admin_data : {}
            };
        },
        mounted() {
            this.getUser();
            this.getCountries();
        },
        methods: {
            proceed(){
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
            getCountrySelectedStatus(country) {
                if (country == this.adminForm.country) {
                    return true;
                } else {
                    return false;
                }
            },
            changePassword() {
                this.passwordForm.post('/admin/user/change-password').then(response => {
                    toastr['success'](response.message);
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
            },
            getUser(){
                axios.get('/admin/user/profile')
                .then(response => {
                    this.admin_data = response.data.data;
                    this.adminForm = new Form({
                        'avatar' : '',
                        'email' : '',
                        'full_name' : '',
                        'street_address' : '',
                        'postal_code' : '',
                        'country' : '',
                        'created_at' : '',
                    }),
                    this.adminForm.avatar = this.admin_data.profile.avatar ? this.admin_data.profile.avatar : "";
                    this.adminForm.email = this.admin_data.email ? this.admin_data.email : "";
                    this.adminForm.full_name = this.admin_data.profile.full_name ? this.admin_data.profile.full_name : "";
                    this.adminForm.street_address = this.admin_data.profile.street_address ? this.admin_data.profile.street_address : "";
                    this.adminForm.postal_code = this.admin_data.profile.postal_code ? this.admin_data.profile.postal_code : "";
                    this.adminForm.country = this.admin_data.profile.country ? this.admin_data.profile.country : "";
                    this.adminForm.created_at = this.admin_data.profile.created_at ? this.admin_data.profile.created_at : "";
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
            uploadAvatar() {
                let data = new FormData();
                data.append('avatar', $('#avatarUpload')[0].files[0]);
                axios.post('/admin/user/update-avatar', data)
                .then(response => {
                    this.$store.dispatch('setAuthUserDetail', {
                        avatar: response.data.profile.avatar
                    });
                    this.adminForm.avatar = response.data.profile.avatar;
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
                });
            },
            updateProfile() {
                this.adminForm.post('/admin/user/update-profile').then(response => {
                    toastr['success'](response.message);
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
                this.getUser();
            },
        },
        computed: {
            getAvatar(){
                if (this.adminForm.avatar) {
                    return '/images/users/'+ this.adminForm.avatar;
                } else {
                    return '/images/common/no-user.png';
                }                
            },
        }
    }
</script>
