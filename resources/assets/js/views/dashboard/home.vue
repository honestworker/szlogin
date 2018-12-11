<template>
    <div>
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Dashboard</h3>
            </div>
        </div>
        
        <!-- Overview -->
        <div class="row">
            <div class="col-lg-3 col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Total Number of Groups</h4>
                        <div class="text-right">
                            <h2 class="font-light m-b-0"><i class="fa fa-group text-success"></i> {{groups_total}}</h2>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Total Number of Users</h4>
                        <div class="text-right">
                            <h2 class="font-light m-b-0"><i class="fa fa-user text-success"></i> {{users_total}}</h2>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Visitor -->
            <div class="col-lg-3 col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">App visitors in real time</h4>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Visits</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Last 24 hours</td>
                                    <td>{{users_visitor_infor[0]}}</td>
                                </tr>
                                <tr>
                                    <td>Last 7 days</td>
                                    <td>{{users_visitor_infor[1]}}</td>
                                </tr>
                                <tr>
                                    <td>Last 30 days</td>
                                    <td>{{users_visitor_infor[2]}}</td>
                                </tr>
                                <tr>
                                    <td>This year</td>
                                    <td>{{users_visitor_infor[3]}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Graph of visitors month by month</h4>
                        <div class="col-lg-9 col-md-9">
                            <GChart type="LineChart" :data="visitorChartData" :options="visitorChartOptions" ref="visitorChart"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Group and User Detail -->
        <div class="row">
            <div class="col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Group Information</h4>
                    </div>
                    <div class="card-body">
                        <h4 class="card-title text-center">New registered groups {{year}}</h4>
                        <div class="row">
                            <div class="col-lg-3 col-md-3">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(group_infor, index) in groups_infor">
                                            <td v-text="monthNames[index]"></td>
                                            <td v-text="group_infor"></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <span class="text-danger">Total this year {{groups_total}}</span>
                            </div>
                            <div class="col-lg-9 col-md-9">
                                <GChart type="ColumnChart":data="groupChartData" :options="groupChartOptions" ref="groupChart"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">User Information</h4>
                    </div>
                    <div class="card-body">
                        <h4 class="card-title text-center">New registered users {{year}}</h4>
                        <div class="row">
                            <div class="col-lg-3 col-md-3">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(infor, index) in users_infor">
                                            <td v-text="monthNames[index]"></td>
                                            <td v-text="infor"></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <span class="text-danger">Total this year {{users_total}}</span>
                            </div>
                            <div class="col-lg-9 col-md-9">
                                <div class="card-body">
                                    <GChart type="ColumnChart" :data="userChartData" :options="userChartOptions" ref="userChart"/>
                                </div>
                                <div class="card-body">
                                    <GChart type="PieChart" :data="userPieChartData" :options="userPieChartOptions" ref="userPieChart"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Adversitement -->
        <div class="row">
            <div class="col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Advertisements Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6 col-md-6">
                                <h4 class="card-title text-center">Number of advertisement running month by month</h4>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Unique Quantity</th>
                                            <th>Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(show_count, index) in show_count_infor">
                                            <td v-text="monthNames[index]"></td>
                                            <td v-text="show_count[0]"></td>
                                            <td v-text="show_count[1]"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-lg-6 col-md-6">
                                <h4 class="card-title text-center">Clicked advertisements</h4>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Unique Quantity</th>
                                            <th>Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(click_count, index) in click_count_infor">
                                            <td v-text="monthNames[index]"></td>
                                            <td v-text="click_count[0]"></td>
                                            <td v-text="click_count[1]"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title text-center">Advertisements statistics</h4>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Ads Banner</th>
                                    <th>Views</th>
                                    <th>Unique Views</th>
                                    <th>Views 30 days</th>
                                    <th>Unique Views 30 days</th>
                                    <th>Clicks</th>
                                    <th>Unique Clicks</th>
                                    <th>Clicks 30 days</th>
                                    <th>Unique Clicks 30 days</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(statistic, index) in statistics">
                                    <td v-text="statistic[0]"></td>
                                    <td v-text="statistic[1] ? statistic[1] : 0"></td>
                                    <td v-text="statistic[2] ? statistic[2] : 0"></td>
                                    <td v-text="statistic[3] ? statistic[3] : 0"></td>
                                    <td v-text="statistic[4] ? statistic[4] : 0"></td>
                                    <td v-text="statistic[5] ? statistic[5] : 0"></td>
                                    <td v-text="statistic[6] ? statistic[6] : 0"></td>
                                    <td v-text="statistic[7] ? statistic[7] : 0"></td>
                                    <td v-text="statistic[8] ? statistic[8] : 0"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import helper from '../../services/helper'
    import ClickConfirm from 'click-confirm'
    import { GChart } from 'vue-google-charts'

    export default {
        data(){
            return {
                monthNames: [
                    "Jan", "Feb", "Mar", "Apr", "May", "Jun",  "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
                ],
                
                users_total: 0,
                users_infor: [],
                userChartData: [],
                
                activated_users: 0,
                userPieChartData: [],
                
                users_visitor_infor: [],
                users_visitors_infor: [],
                visitorChartData: [],

                groups_total: 0,
                groups_infor: [],
                groupChartData: [],
                year: '',

                show_count_infor: [],
                click_count_infor: [],
                statistics: [],

                userChartOptions: {
                    chart: {
                        title: 'Company Performance',
                        subtitle: 'Sales, Expenses, and Profit: 2014-2017',
                    }
                },

                userPieChartOptions: {
                    chart: {
                        title: 'Company Performance',
                        subtitle: 'Sales, Expenses, and Profit: 2014-2017',
                    }
                },

                visitorChartOptions: {
                    chart: {
                        title: 'Company Performance',
                        subtitle: 'Sales, Expenses, and Profit: 2014-2017',
                    }
                },

                groupChartOptions: {
                    chart: {
                        title: 'Company Performance',
                        subtitle: 'Sales, Expenses, and Profit: 2014-2017',
                    }
                }
            }
        },
        ready: function () {
            this.loadData();

            setInterval(function () {
                this.loadData();
            }.bind(this), 100); 
        },
        components : { GChart, ClickConfirm },
        mounted() {
            this.getGroupInfor();
            this.getUserInfor();
            this.getAdsCountsInfor();
        },
        methods: {
            getGroupInfor(){
                axios.post('/api/group/overview').then(response =>  {
                    this.groups_total = response.data.data.total;
                    this.groups_infor = response.data.data.infor;
                    this.year = response.data.data.year;

                    this.groupChartData[0] = ["Month", "Quantity"];
                    for (var index = 1; index <= this.groups_infor.length; index++){
                        this.groupChartData[index] = [this.monthNames[index - 1], this.groups_infor[index - 1]];
                    }
                    this.$refs.groupChart.drawChart();
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
            getUserInfor(){
                axios.get('/api/user/overview').then(response =>  {
                    this.users_total = response.data.data.total;
                    this.users_infor = response.data.data.infor;
                    this.activated_users = response.data.data.activated_users;
                    this.users_visitor_infor = response.data.data.visitor_infor;
                    this.users_visitors_infor = response.data.data.visitors_infor;

                    this.userChartData[0] = ["Month", "Quantity"];
                    for (var index = 1; index <= this.users_infor.length; index++){
                        this.userChartData[index] = [this.monthNames[index - 1], this.users_infor[index - 1]];
                    }
                    this.$refs.userChart.drawChart();

                    this.visitorChartData[0] = ["Year", (this.year - 1) + "", this.year];
                    for (var index = 1; index <= this.groups_infor.length; index++){
                        this.visitorChartData[index] = [this.monthNames[index - 1], this.users_visitors_infor[0][index - 1], this.users_visitors_infor[1][index - 1]];
                    }
                    this.$refs.visitorChart.drawChart();

                    this.userPieChartData = [["Name", "Value"]];
                    this.userPieChartData.push(["Active last 30 days", this.activated_users]);
                    this.userPieChartData.push(["Non active last 30 days", this.users_total - this.activated_users]);
                    this.$refs.userPieChart.drawChart();
                    
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
            getAdsCountsInfor(){
                axios.post('/api/advertisement/overview').then(response =>  {
                    this.show_count_infor = response.data.data.show_count_infor;
                    this.click_count_infor = response.data.data.click_count_infor;
                    this.statistics = response.data.data.statistics;
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
            loadData() {
                this.getGroupInfor();
                this.getUserInfor();
                this.getAdsCountsInfor();
                this.redraw();
            }
        },
        computed: {
        },
        filters: {
            moment(date) {
                return helper.formatDate(date);
            },
            momentWithTime(date) {
                return helper.formatDateTime(date);
            }
        }
    }
</script>
<style>
    .strikethrough{
      text-decoration: line-through;
    }
</style>
