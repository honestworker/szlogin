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
                                <strong><span>Email: </span></strong>
                                <span v-text="adminForm.email"></span>
                            </div>
                            <div class="form-group">
                                <strong><span>Created At: </span></strong>
                                <span v-text="adminForm.created_at"></span>
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
                            <!-- <div class="form-group">
                                <strong><span>Full Name: </span></strong>
                                <input class="form-control" type="text" value="" v-model="adminForm.full_name">
                            </div>
                            <div class="form-group">
                                <strong><span>Group Name: </span></strong>
                                <input class="form-control" type="text" value="" v-model="adminForm.group_name">
                            </div>
                            <div class="form-group">
                                <strong><span>Organization Number: </span></strong>
                                <input class="form-control" type="text" value="" v-model="adminForm.org_number">
                            </div> -->
                            <div class="form-group">
                                <strong><span>Full Name: </span></strong>
                                <span v-text="adminForm.full_name"></span>
                            </div>
                            <div class="form-group">
                                <strong><span>First Name: </span></strong>
                                <input class="form-control" type="text" value="" v-model="adminForm.first_name">
                            </div>
                            <div class="form-group">
                                <strong><span>Fmaily Name: </span></strong>
                                <input class="form-control" type="text" value="" v-model="adminForm.family_name">
                            </div>
                            <div class="form-group">
                                <strong><span>Phone Number: </span></strong>
                                <input class="form-control" type="text" value="" v-model="adminForm.phone_number">
                            </div>
                            <div class="form-group">
                                <strong><span>Street Address: </span></strong>
                                <input class="form-control" type="text" value="" v-model="adminForm.street_address">
                            </div>
                            <!-- <div class="form-group">
                                <strong><span>Street Number: </span></strong>
                                <input class="form-control" type="text" value="" v-model="adminForm.street_number">
                            </div> -->
                            <div class="form-group">
                                <strong><span>Postal Code: </span></strong>
                                <input class="form-control" type="text" value="" v-model="adminForm.postal_code">
                            </div>
                            <div class="form-group">
                                <strong><span>Country: </span></strong>
                                <select name="status" class="form-control" v-model="adminForm.country">
                                    <option value="">None</option>
                                    <option v-for="country in countries.countries" v-bind:value="country" v-bind:selected="getCountrySelectedStatus(country)">{{country}}</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <strong><span>City: </span></strong>
                                <input class="form-control" type="text" value="" v-model="adminForm.city">
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
                // adminForm: new Form({
                //     // 'contact_person' : '',
                //     'avatar' : '',
                //     // 'group_name' : '',
                //     // 'org_number' : '',
                //     'email' : '',
                //     'full_name' : '',
                //     'first_name' : '',
                //     'family_name' : '',
                //     'full_name' : '',
                //     'phone_number' : '',
                //     'street_address' : '',
                //     // 'street_number' : '',
                //     'postal_code' : '',
                //     'country' : '',
                //     'city' : '',
                //     'created_at' : '',
                // }),
                passwordForm: new Form({
                    'current_password' : '',
                    'new_password' : '',
                    'new_password_confirmation' : '',
                }),
                user_data : {}
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
                axios.post('/api/country/all').then(response => {
                    this.countries = response.data;
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
            getCountrySelectedStatus(country) {
                if (country == this.adminForm.country) {
                    return true;
                } else {
                    return false;
                }
            },
            changePassword() {
                this.passwordForm.post('/api/user/change-password').then(response => {
                    toastr['success'](response.message);
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
            getUser(){
                axios.post('/api/user/profile')
                .then(response => {
                    this.user_data = response.data.data;
                    // this.adminForm.contact_person =  this.user_data.profile.contact_person ? this.user_data.profile.contact_person : "";
                    this.adminForm = new Form({
                        // 'contact_person' : '',
                        'avatar' : '',
                        // 'group_name' : '',
                        // 'org_number' : '',
                        'email' : '',
                        'full_name' : '',
                        'first_name' : '',
                        'family_name' : '',
                        'full_name' : '',
                        'phone_number' : '',
                        'street_address' : '',
                        // 'street_number' : '',
                        'postal_code' : '',
                        'country' : '',
                        'city' : '',
                        'created_at' : '',
                    }),
                    this.adminForm.avatar = this.user_data.profile.avatar ? this.user_data.profile.avatar : "";
                    // this.adminForm.group_name = this.user_data.profile.group_name ? this.user_data.profile.group_name : "";
                    // this.adminForm.org_number = this.user_data.profile.org_number ? this.user_data.profile.org_number : "";
                    this.adminForm.email = this.user_data.email ? this.user_data.email : "";
                    this.adminForm.first_name = this.user_data.profile.first_name ? this.user_data.profile.first_name : "";
                    this.adminForm.family_name = this.user_data.profile.family_name ? this.user_data.profile.family_name : "";
                    this.adminForm.full_name = this.user_data.profile.full_name ? this.user_data.profile.full_name : "";
                    this.adminForm.phone_number = this.user_data.profile.phone_number ? this.user_data.profile.phone_number : "";
                    this.adminForm.street_address = this.user_data.profile.street_address ? this.user_data.profile.street_address : "";
                    // this.adminForm.street_number = this.user_data.profile.street_number ? this.user_data.profile.street_number : "";
                    this.adminForm.postal_code = this.user_data.profile.postal_code ? this.user_data.profile.postal_code : "";
                    this.adminForm.country = this.user_data.profile.country ? this.user_data.profile.country : "";
                    this.adminForm.city = this.user_data.profile.city ? this.user_data.profile.city : "";
                    this.adminForm.created_at = this.user_data.profile.created_at ? this.user_data.profile.created_at : "";
                    console.log("OK");
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
            uploadAvatar() {
                let data = new FormData();
                data.append('avatar', $('#avatarUpload')[0].files[0]);
                axios.post('/api/user/update-avatar', data)
                .then(response => {
                    this.$store.dispatch('setAuthUserDetail', {
                        avatar: response.data.profile.avatar
                    });
                    this.adminForm.avatar = response.data.profile.avatar;
                    toastr['success'](response.data.message);
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
            updateProfile() {
                this.adminForm.post('/api/user/update-profile').then(response => {
                    toastr['success'](response.message);
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
