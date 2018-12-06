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
                            </div>
                            <!-- <div class="form-group">
                                <strong><span>Contact Person: </span></strong>
                                <span v-text="userForm.contact_person"></span>
                            </div> -->
                            <div class="form-group">
                                <strong><span>Full Name: </span></strong>
                                <span v-text="userForm.full_name"></span>
                            </div>
                            <div class="form-group">
                                <strong><span>Email: </span></strong>
                                <span v-text="userForm.email"></span>
                            </div>
                            <!-- <div class="form-group">
                                <strong><span>Group Name: </span></strong>
                                <span v-text="userForm.group_name"></span>
                            </div>
                            <div class="form-group">
                                <strong><span>Organization Number: </span></strong>
                                <span v-text="userForm.org_number"></span>
                            </div> -->
                            <div class="form-group">
                                <strong><span>User Role: </span></strong>
                                <span v-text="userForm.role"></span>
                            </div>
                            <div class="form-group">
                                <strong><span>Created At: </span></strong>
                                <span v-text="userForm.created_at"></span>
                            </div>
                        </div>
                    </div>
                    <router-link to="/user" class="btn btn-danger waves-effect waves-light m-t-10" v-show="id">Cancel</router-link>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <strong><span>Group ID: </span></strong>
                                <span v-text="userForm.group_id"></span>
                            </div>
                            <div class="form-group">
                                <strong><span>First Name: </span></strong>
                                <span v-text="userForm.first_name"></span>
                            </div>
                            <div class="form-group">
                                <strong><span>Fmaily Name: </span></strong>
                                <span v-text="userForm.family_name"></span>
                            </div>
                            <div class="form-group">
                                <strong><span>Phone Number: </span></strong>
                                <span v-text="userForm.phone_number"></span>
                            </div>
                            <div class="form-group">
                                <strong><span>Street Address: </span></strong>
                                <span v-text="userForm.street_address"></span>
                            </div>
                            <!-- <div class="form-group">
                                <strong><span>Street Number: </span></strong>
                                <span v-text="userForm.street_number"></span>
                            </div> -->
                            <div class="form-group">
                                <strong><span>Postal Code: </span></strong>
                                <span v-text="userForm.postal_code"></span>
                            </div>
                            <div class="form-group">
                                <strong><span>Country: </span></strong>
                                <span v-text="userForm.country"></span>
                            </div>
                            <div class="form-group">
                                <strong><span>City: </span></strong>
                                <span v-text="userForm.city"></span>
                            </div>
                        </div>
                    </div>
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
                    // 'street_number' : '',
                    'postal_code' : '',
                    'country' : '',
                    'city' : '',
                    'created_at' : '',
                    'role' : '',
                    'group_id' : '',
                }),
                user_data : {}
            };
        },
        props: ['id'],
        mounted() {
            if(this.id != 0)
                this.getUser();
        },
        methods: {
            proceed(){
                if(this.id != 0)
                    this.getUser();
            },
            getUser(){
                axios.post('/api/user/'+this.id)
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
                    // this.userForm.street_number = this.user_data.profile.street_number ? this.user_data.profile.street_number : "";
                    this.userForm.postal_code = this.user_data.profile.postal_code ? this.user_data.profile.postal_code : "";
                    this.userForm.country = this.user_data.profile.country ? this.user_data.profile.country : "";
                    this.userForm.city = this.user_data.profile.city ? this.user_data.profile.city : "";
                    this.userForm.created_at = this.user_data.profile.created_at ? this.user_data.profile.created_at : "";
                    this.userForm.role = (this.user_data.profile.is_admin == 1) ? "Manager" : "User";
                    this.userForm.group_id = this.user_data.group_id ? this.user_data.group_id : "";
                }).catch(error => {
                    if (error.response.data) {
                        if (error.response.data.error_type == 'token_error') {
                            toastr['error']('The token is expired! Please refresh and try again!');
                            this.$router.push('/login');
                        } else {
                            if (error.response.data.message) {
                                toastr['error'](error.response.data.message);
                            } else {
                                toastr['error']('An unexpected error occurred!');
                            }
                        } 
                    } else {
                        toastr['error']('An unexpected error occurred!');
                    }
                });
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
