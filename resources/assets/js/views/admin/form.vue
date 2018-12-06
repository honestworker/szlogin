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
                                <span v-text="adminForm.contact_person"></span>
                            </div> -->
                            <div class="form-group">
                                <strong><span>Full Name: </span></strong>
                                <span v-text="adminForm.full_name"></span>
                            </div>
                            <div class="form-group">
                                <strong><span>Email: </span></strong>
                                <span v-text="adminForm.email"></span>
                            </div>
                            <!-- <div class="form-group">
                                <strong><span>Group Name: </span></strong>
                                <span v-text="adminForm.group_name"></span>
                            </div>
                            <div class="form-group">
                                <strong><span>Organization Number: </span></strong>
                                <span v-text="adminForm.org_number"></span>
                            </div> -->
                            <div class="form-group">
                                <strong><span>Created At: </span></strong>
                                <span v-text="adminForm.created_at"></span>
                            </div>
                        </div>
                    </div>
                    <router-link to="/admin" class="btn btn-danger waves-effect waves-light m-t-10" v-show="id">Cancel</router-link>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <strong><span>First Name: </span></strong>
                                <span v-text="adminForm.first_name"></span>
                            </div>
                            <div class="form-group">
                                <strong><span>Fmaily Name: </span></strong>
                                <span v-text="adminForm.family_name"></span>
                            </div>
                            <div class="form-group">
                                <strong><span>Phone Number: </span></strong>
                                <span v-text="adminForm.phone_number"></span>
                            </div>
                            <div class="form-group">
                                <strong><span>Street Address: </span></strong>
                                <span v-text="adminForm.street_address"></span>
                            </div>
                            <!-- <div class="form-group">
                                <strong><span>Street Number: </span></strong>
                                <span v-text="adminForm.street_number"></span>
                            </div> -->
                            <div class="form-group">
                                <strong><span>Postal Code: </span></strong>
                                <span v-text="adminForm.postal_code"></span>
                            </div>
                            <div class="form-group">
                                <strong><span>Country: </span></strong>
                                <span v-text="adminForm.country"></span>
                            </div>
                            <div class="form-group">
                                <strong><span>City: </span></strong>
                                <span v-text="adminForm.city"></span>
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
                adminForm: new Form({
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
                admin_data : {}
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
                    this.admin_data = response.data.data;
                    // this.adminForm.contact_person =  this.admin_data.profile.contact_person ? this.admin_data.profile.contact_person : "";
                    this.adminForm.avatar = this.admin_data.profile.avatar ? this.admin_data.profile.avatar : "";
                    // this.adminForm.group_name = this.admin_data.profile.group_name ? this.admin_data.profile.group_name : "";
                    // this.adminForm.org_number = this.admin_data.profile.org_number ? this.admin_data.profile.org_number : "";
                    this.adminForm.email = this.admin_data.email ? this.admin_data.email : "";
                    this.adminForm.first_name = this.admin_data.profile.first_name ? this.admin_data.profile.first_name : "";
                    this.adminForm.family_name = this.admin_data.profile.family_name ? this.admin_data.profile.family_name : "";
                    this.adminForm.full_name = this.admin_data.profile.full_name ? this.admin_data.profile.full_name : "";
                    this.adminForm.phone_number = this.admin_data.profile.phone_number ? this.admin_data.profile.phone_number : "";
                    this.adminForm.street_address = this.admin_data.profile.street_address ? this.admin_data.profile.street_address : "";
                    // this.adminForm.street_number = this.admin_data.profile.street_number ? this.admin_data.profile.street_number : "";
                    this.adminForm.postal_code = this.admin_data.profile.postal_code ? this.admin_data.profile.postal_code : "";
                    this.adminForm.country = this.admin_data.profile.country ? this.admin_data.profile.country : "";
                    this.adminForm.city = this.admin_data.profile.city ? this.admin_data.profile.city : "";
                    this.adminForm.created_at = this.admin_data.profile.created_at ? this.admin_data.profile.created_at : "";
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
                if (this.adminForm.avatar) {
                    return '/images/users/'+ this.adminForm.avatar;
                } else {
                    return '/images/common/no-user.png';
                }
                
            }
        }
    }
</script>
