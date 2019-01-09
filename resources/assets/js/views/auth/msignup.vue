<template>
    <section id="wrapper">
        <div class="login-signup" style="background-image:url(/images/background/background.jpg);">
            <div class="login-box card">
            <div class="card-body">
                <form class="form-horizontal form-material" id="signform" @submit.prevent="submit">
                    <h3 class="box-title text-center m-b-20">Sign Up</h3>
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input type="text" name="group_name" class="form-control" placeholder="Group Name" v-model="signupForm.group_name">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <input type="text" name="email" class="form-control" placeholder="Username(Email)" v-model="signupForm.email">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <input type="password" name="password" class="form-control" placeholder="Password" v-model="signupForm.password"> </div>
                    </div>
                    <!-- <div class="form-group">
                        <div class="col-xs-12">
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password" v-model="signupForm.password_confirmation"> </div>
                    </div> -->
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input type="text" name="first_name" class="form-control" placeholder="First Name" v-model="signupForm.first_name">
                        </div>
                    </div>
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input type="text" name="family_name" class="form-control" placeholder="Family Name" v-model="signupForm.family_name">
                        </div>
                    </div>
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input type="text" name="street_address" class="form-control" placeholder="Street Address" v-model="signupForm.street_address">
                        </div>
                    </div>
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input type="text" name="street_number" class="form-control" placeholder="Street Number" v-model="signupForm.street_number">
                        </div>
                    </div>
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input type="text" name="postal_code" class="form-control" placeholder="Postal Code" v-model="signupForm.postal_code">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <input type="text" name="phone_number" class="form-control" placeholder="Mobile Number" v-model="signupForm.phone_number">
                        </div>
                    </div>
                    <div class="form-group text-center m-t-20">
                        <div class="col-xs-12">
                            <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Sign Up</button>
                        </div>
                    </div>
                    <div class="form-group m-b-0">
                        <div class="col-sm-12 text-center">
                            <p>Already have an account?</p>
                            <p><router-link to="/login" class="text-info m-l-5"><b>Log In</b></router-link></p>
                        </div>
                    </div>
                    <div class="form-group m-b-0">
                        <div class="col-sm-12 text-center">
                            <p>Don't have an account?</p>
                            <p><router-link to="/register" class="text-info m-l-5"><b>Register</b></router-link></p>
                        </div>
                    </div>
                </form>
            </div>
            <guest-footer></guest-footer>
          </div>
        </div>

    </section>
</template>

<script>
    import GuestFooter from '../../layouts/guest-footer.vue'

    export default {
        data() {
            return {
                signupForm: {
                    group_name: '',
                    email: '',
                    password: '',
                    first_name: '',
                    family_name: '',
                    street_address: '',
                    street_number: '',
                    postal_code: '',
                    phone_number: '',
                }
            }
        },
        components: {
            GuestFooter
        },
        mounted(){
        },
        methods: {
            submit(e){
                axios.post('/api/auth/signup', this.signupForm).then(response =>  {
                    toastr['success'](response.data.message);
                    this.$router.push('/success_signup');
                }).catch(error => {
                    if (error.response.data.status == 'fail') {
                        toastr['error'](error.response.data.message);
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
