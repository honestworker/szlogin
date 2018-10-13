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
                                <span v-text="userForm.email"></span>
                            </div>
                            <div class="form-group">
                                <strong><span>User Role: </span></strong>
                                <span v-text="userForm.role"></span>
                            </div>
                            <div class="form-group">
                                <strong><span>Created At: </span></strong>
                                <span v-text="userForm.created_at"></span>
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
                                <strong><span>Contact Person: </span></strong>
                                <input class="form-control" type="text" value="" v-model="userForm.contact_person">
                            </div>
                            <div class="form-group">
                                <strong><span>Group Name: </span></strong>
                                <input class="form-control" type="text" value="" v-model="userForm.group_name">
                            </div>
                            <div class="form-group">
                                <strong><span>Organization Number: </span></strong>
                                <input class="form-control" type="text" value="" v-model="userForm.org_number">
                            </div> -->
                            <div class="form-group">
                                <strong><span>Group ID: </span></strong>
                                <select name="groups" class="form-control">
                                    <option value="0">None</option>
                                    <option v-for="group in groups.data" v-bind:value="group.id" v-bind:selected="getGroupSelectedStatus(group.group_id)">{{ group.group_id }}</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <strong><span>Full Name: </span></strong>
                                <input class="form-control" type="text" value="" v-model="userForm.full_name">
                            </div>
                            <div class="form-group">
                                <strong><span>First Name: </span></strong>
                                <input class="form-control" type="text" value="" v-model="userForm.first_name">
                            </div>
                            <div class="form-group">
                                <strong><span>Fmaily Name: </span></strong>
                                <input class="form-control" type="text" value="" v-model="userForm.family_name">
                            </div>
                            <div class="form-group">
                                <strong><span>Phone Number: </span></strong>
                                <input class="form-control" type="text" value="" v-model="userForm.phone_number">
                            </div>
                            <div class="form-group">
                                <strong><span>Street Address: </span></strong>
                                <input class="form-control" type="text" value="" v-model="userForm.street_address">
                            </div>
                            <div class="form-group">
                                <strong><span>Street Number: </span></strong>
                                <input class="form-control" type="text" value="" v-model="userForm.street_number">
                            </div>
                            <div class="form-group">
                                <strong><span>Postal Code: </span></strong>
                                <input class="form-control" type="text" value="" v-model="userForm.postal_code">
                            </div>
                            <div class="form-group">
                                <strong><span>Country: </span></strong>
                                <select name="status" class="form-control" v-model="userForm.country">
                                    <option value="">None</option>
                                    <option v-for="country in countries.countries" v-bind:value="country" v-bind:selected="getCountrySelectedStatus(country)">{{country}}</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <strong><span>City: </span></strong>
                                <input class="form-control" type="text" value="" v-model="userForm.city">
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
                userForm: new Form({
                    // 'contact_person' : '',
                    'avatar' : '',
                    // 'group_name' : '',
                    // 'org_number' : '',
                    'email' : '',
                    'first_name' : '',
                    'family_name' : '',
                    'full_name' : '',
                    'phone_number' : '',
                    'street_address' : '',
                    'street_number' : '',
                    'postal_code' : '',
                    'country' : '',
                    'city' : '',
                    'created_at' : '',
                    'role' : '',
                    'group_id' : '',
                }),
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
            this.getGroups();
        },
        methods: {
            proceed(){
            },
            getCountries() {
                axios.post('/api/country/all')
                    .then(response => this.countries = response.data);
            },
            getCountrySelectedStatus(country) {
                if (country == this.userForm.country) {
                    return true;
                } else {
                    return false;
                }
            },
            getGroups() {
                axios.post('/api/group/all').then(response => {
                    this.groups = response.data;
                });
            },
            getGroupSelectedStatus(group_id) {
                if (group_id == this.userForm.group_id) {
                    return true;
                } else {
                    return false;
                }
            },
            changePassword() {
                this.passwordForm.post('/api/user/change-password').then(response => {
                    toastr['success'](response.message);
                }).catch(response => {
                    toastr['error'](response.message);
                });
            },
            getUser(){
                axios.post('/api/user/profile')
                .then(response => {
                    this.user_data = response.data.data;
                    // this.userForm.contact_person =  this.user_data.profile.contact_person ? this.user_data.profile.contact_person : "";
                    this.userForm.avatar = this.user_data.profile.avatar ? this.user_data.profile.avatar : "";
                    // this.userForm.group_name = this.user_data.profile.group_name ? this.user_data.profile.group_name : "";
                    // this.userForm.org_number = this.user_data.profile.org_number ? this.user_data.profile.org_number : "";
                    this.userForm.email = this.user_data.email ? this.user_data.email : "";
                    this.userForm.first_name = this.user_data.profile.first_name ? this.user_data.profile.first_name : "";
                    this.userForm.family_name = this.user_data.profile.family_name ? this.user_data.profile.family_name : "";
                    this.userForm.full_name = this.userForm.first_name + " " + this.userForm.family_name;
                    this.userForm.phone_number = this.user_data.profile.phone_number ? this.user_data.profile.phone_number : "";
                    this.userForm.street_address = this.user_data.profile.street_address ? this.user_data.profile.street_address : "";
                    this.userForm.street_number = this.user_data.profile.street_number ? this.user_data.profile.street_number : "";
                    this.userForm.postal_code = this.user_data.profile.postal_code ? this.user_data.profile.postal_code : "";
                    this.userForm.country = this.user_data.profile.country ? this.user_data.profile.country : "";
                    this.userForm.city = this.user_data.profile.city ? this.user_data.profile.city : "";
                    this.userForm.created_at = this.user_data.profile.created_at ? this.user_data.profile.created_at : "";
                    this.userForm.role = this.user_data.role ? this.user_data.role : "";
                    this.userForm.group_id = this.user_data.group_id ? this.user_data.group_id : "";
                })
                .catch(response => {
                    toastr['error'](response.message);
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
                    this.userForm.avatar = response.data.profile.avatar;
                    toastr['success'](response.data.message);
                }).catch(error => {
                    toastr['error'](error.response.data.message);
                });
            },
            updateProfile() {
                this.userForm.post('/api/user/update-profile').then(response => {
                    toastr['success'](response.message);
                }).catch(response => {
                    toastr['error'](response.message);
                });
                this.getUser();
            },
        },
        computed: {
            getAvatar(){
                if (this.userForm.avatar) {
                    return '/images/users/'+ this.userForm.avatar;
                } else {
                    return '/images/common/no-user.png';
                }
                
            }
        }
    }
</script>
