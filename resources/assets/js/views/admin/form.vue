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
                        </div>
                    </div>
                    <router-link to="/admin" class="btn btn-danger waves-effect waves-light m-t-10" v-show="id">Cancel</router-link>
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
                                        <span v-text="adminForm.full_name"></span>
                                    </div>
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
                                        <strong><span>Street Address: </span></strong>
                                    </div>
                                    <div class="col-lg-7">
                                        <span v-text="adminForm.street_address"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-5 text-right">
                                        <strong><span>Postal Code: </span></strong>
                                    </div>
                                    <div class="col-lg-7">
                                        <span v-text="adminForm.postal_code"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-5 text-right">
                                        <strong><span>Country: </span></strong>
                                    </div>
                                    <div class="col-lg-7">
                                        <span v-text="adminForm.country"></span>
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
                    'avatar' : '',
                    'email' : '',
                    'full_name' : '',
                    'street_address' : '',
                    'postal_code' : '',
                    'country' : '',
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
                axios.get('/admin/user/'+this.id)
                .then(response => {
                    this.admin_data = response.data.data;
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
