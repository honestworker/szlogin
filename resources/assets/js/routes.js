import VueRouter from 'vue-router'
import helper from './services/helper'

let routes = [
    {
        path: '/',
        component: require('./layouts/default-page'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '/',
                component: require('./views/pages/home')
            },
            {
                path: '/home',
                component: require('./views/pages/home')
            },
            {
                path: '/configuration',
                component: require('./views/configuration/configuration')
            },
            {
                path: '/group',
                component: require('./views/group/index')
            },
            {
                path: '/profile',
                component: require('./views/user/profile')
            },
            {
                path: '/group/:id/edit',
                component: require('./views/group/edit')
            },
            {
                path: '/user',
                component: require('./views/user/index')
            },
            {
                path: '/user/:id/view',
                component: require('./views/user/view')
            },
            {
                path: '/user/new',
                component: require('./views/user/new')
            },
            {
                path: '/country',
                component: require('./views/country/index')
            },
            {
                path: '/notification',
                component: require('./views/notification/index')
            },
            {
                path: '/noti_type',
                component: require('./views/notification/type')
            },
            {
                path: '/advertisement',
                component: require('./views/advertisement/index')
            },
            {
                path: '/advertisement/:id',
                component: require('./views/advertisement/edit')
            },
        ]
    },
    {
        path: '/',
        component: require('./layouts/guest-page'),
        meta: { requiresGuest: true },
        children: [
            {
                path: '/login',
                component: require('./views/auth/login')
            },
            {
                path: '/password',
                component: require('./views/auth/password')
            },
            {
                path: '/register',
                component: require('./views/auth/register')
            },
            {
                path: '/success_register',
                component: require('./views/auth/success_register')
            },
            {
                path: '/signup',
                component: require('./views/auth/signup')
            },
            {
                path: '/msignup',
                component: require('./views/auth/msignup')
            },
            {
                path: '/success_signup',
                component: require('./views/auth/success_signup')
            },
            {
                path: '/auth/:token/activate',
                component: require('./views/auth/activate')
            },
            {
                path: '/password/reset/:token',
                component: require('./views/auth/reset')
            },
            {
                path: '/auth/social',
                component: require('./views/auth/social-auth')
            },
        ]
    },
    {
        path: '*',
        component : require('./layouts/error-page'),
        children: [
            {
                path: '*',
                component: require('./views/errors/page-not-found')
            }
        ]
    }
];

const router = new VueRouter({
	routes,
    linkActiveClass: 'active',
    mode: 'history'
});

router.beforeEach((to, from, next) => {

    if (to.matched.some(m => m.meta.requiresAuth)){
        return helper.check().then(response => {
            if(!response) {
                return next({ path : '/login'})
            }
            
            return next()
        })
    }

    if (to.matched.some(m => m.meta.requiresGuest)){
        return helper.check().then(response => {
            if(response){
                return next({ path : '/'})
            }
            
            return next()
        })
    }

    return next()
});

export default router;
