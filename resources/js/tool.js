Nova.booting((Vue, router, store) => {
    router.addRoutes([
        {
            name: 'nova-passport-manager',
            path: '/nova-passport-manager',
            component: require('./components/Tool'),
        },
    ])
})
