import axios from 'axios';
window.axios = axios;


// Axios is a JavaScript HTTP client - this is what frontend uses to make requests to fackend (fetch, etc) 
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
