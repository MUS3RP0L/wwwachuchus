
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('./inspinia');
require('print-js');

window.Vue = require('vue');

window.events = new Vue();
window.flash = function (message, level = 'success', timeOut = 5000) {
	window.events.$emit('flash', { message, level, timeOut});
};

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

 /**VUEX */
 import store from './store/index';

import VueFormWizard from 'vue-form-wizard';
import 'vue-form-wizard/dist/vue-form-wizard.min.css';
Vue.use(VueFormWizard);

import VueSweetalert2 from 'vue-sweetalert2';
Vue.use(VueSweetalert2);

import Vuetify from 'vuetify'

Vue.use(Vuetify)

import VeeValidate, { Validator } from 'vee-validate';
import es from "vee-validate/dist/locale/es";
Vue.use(VeeValidate, {
  locale: "es",
  fieldsBagName: "vFields",
  dictionary: {
    es: {
      custom: {
        procedure_type_id: {
          required: "Debe seleccionar el tipo de Pago para el Trámite."
        },
        city_end_id: {
          required: "Debe seleccionar la regional del Trámite."
        },
        ret_fun_modality: {
          required: "Debe seleccionar la modalidad del Trámite."
        },
        accountType: {
          required: "Debe seleccionar el tipo de solicitante."
        },
        applicant_identity_card: {
          required: "Debe escribir el ci del solicitante."
        },
        date_derelict: {
          required: "Debe ingresar fecha de desvinculación."
        },
        applicant_city_identity_card: {
          required:
            "Debe seleccionar la ciudad de expedición del ci del solicitante."
        },
        applicant_kinship: {
          required: "Debe seleccionar el parentesco del solicitante."
        },
        identity_card: {
          required: "Debe ingresar la Cedula de identidad."
        },
        city_identity_card_id: {
          required: "Debe seleccionar la ciudad de expedición."
        },
        first_name: {
          required: "Debe ingresar el primer nombre.",
          alpha_space_quote:
            "El campo primer nombre solo puede contener caracteres alfabéticos o '."
        },
        second_name: {
          alpha_space_quote:
            "El campo segundo nombre solo puede contener caracteres alfabéticos o '."
        },
        last_name: {
          required: "Debe escribir el apellido paterno.",
          alpha_space_quote:
            "El campo primer nombre solo puede contener caracteres alfabéticos o '."
        },
        mothers_last_name: {
          alpha_space_quote:
            "El campo apellido materno solo puede contener caracteres alfabéticos o '."
        },
        surname_husband: {
          alpha_space_quote:
            "El campo apellido de casada solo puede contener caracteres alfabéticos o '."
        },
        gender: {
          required: "Debe seleccionar el genero."
        },
        birth_date: {
          required: "Debe escribir la fecha de Nacimiento.",
          date_format:
            "Debe escribir la fecha de Nacimiento en el formato dia/mes/año."
        },
        service_years: {
          required: "Debe ingresar los años de servicio",
          numeric: "El campo años de servicio debe ser un numero",
          max_value: "El campo años servicio debe ser menor o igual a 12.",
          min_value: "El campo años de servicio debe ser mayor o igual a 0."
        },
        service_months: {
          required: "Debe ingresar los meses de servicio",
          numeric: "El campo meses de servicio debe ser un numero",
          max_value: "El campo meses servicio debe ser menor o igual a 12.",
          min_value: "El campo meses servicio debe ser mayor o igual a 0."
        }
      }
    }
  }
});

//custom rules
Validator.localize({es:es});
let instance = new Validator();
instance.extend('max_date', {
  getMessage: (field) => `La fecha ingresada no es valida.`,
  validate: (value) => {
    return moment().subtract(18, 'years').diff(moment(value, "DD/MM/YYYY"), "days") > 0;
  }
});
instance = new Validator();
instance.extend('alpha_space_quote', {
  getMessage: (field) => `El dato ingresado es incorrecto.`,
  validate: (value) => {
    let regex = /^[A-ZÁÉÍÑÓÚÜ\s\'\.]*$/i;
    return regex.exec(value) !== null;
  }
});

import Vuelidate from 'vuelidate'
Vue.use(Vuelidate)

import VueCurrencyFilter from 'vue-currency-filter';

Vue.use(VueCurrencyFilter,
{
	symbol: 'Bs',
	thousandsSeparator: ',',
	fractionCount: 2,
	fractionSeparator: '.',
	symbolPosition: 'front',
	symbolSpacing: true
});

Vue.filter('percentage', function (value) {
	return `${value.toFixed(2)} %`;
});
moment.locale("es");
Vue.filter('month', function (value) {
  return moment(value).format("MMMM").toString().toUpperCase();
});
Vue.filter('year', function (value) {
  return moment(value).format("YYYY");
});
Vue.filter('formatDateInbox', function (value) {
  return moment(value).format("DD MMMM YYYY");
});
Vue.filter('uppercase', function (value) {
  return value.toUpperCase();
});

//vue mask hdp
import VueTheMask from 'vue-the-mask'
Vue.use(VueTheMask)

/* tabs */
import VueTabs from 'vue-nav-tabs'
import "vue-nav-tabs/themes/vue-tabs.css";
Vue.use(VueTabs)


// import { Tabs, Tab } from 'vue-tabs-component';

// Vue.component('tabs', Tabs);
// Vue.component('tab', Tab);

import VueScrollTo from 'vue-scrollto';

Vue.use(VueScrollTo, {
  container: "body",
  duration: 500,
  easing: "ease",
  offset: 0,
  cancelable: true,
  onStart: false,
  onDone: false,
  onCancel: false,
  x: false,
  y: true
})





/* Components */

Vue.component('flash', require('./components/Flash.vue'));
Vue.component('check-svg', require('./components/CheckSvg.vue'));

//setting files
Vue.component('ret-fun-procedure', require('./components/setting/RetFunProcedure.vue'));

Vue.component('affiliate-index', require('./components/affiliate/Index.vue'));
Vue.component('affiliate-show', require('./components/affiliate/ShowAffiliate.vue'));
Vue.component('affiliate-police', require('./components/affiliate/Police.vue'));
Vue.component('spouse-show', require('./components/spouse/ShowSpouse.vue'));
//retirement Fund

Vue.component('ret-fun-index', require('./components/ret_fun/Index.vue'));
Vue.component('ret-fun-form', require('./components/ret_fun/Form.vue'));
Vue.component('ret-fun-create-info', require('./components/ret_fun/CreateInfo.vue'));
Vue.component('ret-fun-step1-requirements', require('./components/ret_fun/Step1Requirements.vue'));
Vue.component('ret-fun-step1-requirements-edit', require('./components/ret_fun/Step1RequirementsEdit.vue'));
Vue.component('ret-fun-step2-applicant', require('./components/ret_fun/Step2Applicant.vue'));
Vue.component('ret-fun-step3-beneficiaries', require('./components/ret_fun/Step3Beneficiaries.vue'));
Vue.component('ret-fun-beneficiary-list', require('./components/ret_fun/BeneficiaryList.vue'));
Vue.component('ret-fun-beneficiary', require('./components/ret_fun/Beneficiary.vue'));
Vue.component('ret-fun-info', require('./components/ret_fun/Info.vue'));
Vue.component('ret-fun-beneficiaries-show', require('./components/ret_fun/ShowBeneficiaries.vue'));
Vue.component('ret-fun-qualification', require('./components/ret_fun/Qualification.vue'));
Vue.component('ret-fun-date-interval', require('./components/ret_fun/DateInterval.vue'));
Vue.component('ret-fun-qualification-group', require('./components/ret_fun/QualificationGroup.vue'));

// inbox
Vue.component('tabs-content', require('./components/inbox/TabsContent.vue'));
Vue.component('inbox-content', require('./components/inbox/Content.vue'));

//tags
Vue.component('tag-list', require('./components/tag/TagList.vue'));
Vue.component('tag-create', require('./components/tag/Create.vue'));
Vue.component('tag-wf-state', require('./components/tag/WfState.vue'));


// Quota Aid Mortuaries
Vue.component('quota-aid-mortuary-index', require('./components/quota_aid/Index.vue'));
Vue.component('quota-aid-form', require('./components/quota_aid/Form.vue'));
Vue.component('quota-aid-create-info', require('./components/quota_aid/CreateInfo.vue'));

//quota_aid
Vue.component('quota-aid-step1-requirements', require('./components/quota_aid/Step1Requirements.vue'));
Vue.component('quota-aid-step2-applicant', require('./components/quota_aid/Step2Applicant.vue'));
Vue.component('quota-aid-step3-beneficiaries', require('./components/quota_aid/Step3Beneficiaries.vue'));
Vue.component('quota-aid-beneficiary-list', require('./components/quota_aid/BeneficiaryList.vue'));
Vue.component('quota-aid-beneficiary', require('./components/quota_aid/Beneficiary.vue'));
Vue.component('quota-aid-info', require('./components/quota_aid/Info.vue'));
Vue.component('quota-aid-beneficiaries-show', require('./components/quota_aid/ShowBeneficiaries.vue'));
Vue.component('quota-aid-step1-requirements-edit', require('./components/quota_aid/Step1RequirementsEdit.vue'));
//user
Vue.component('show-password', require('./components/user/ShowPassword.vue'));
//permission
Vue.component('nom-module', require('./components/permission/NomModule.vue'));

//contributions
Vue.component('contribution-create', require('./components/contribution/CreateContribution.vue'));
Vue.component('contribution-commitment', require('./components/contribution/Commitment.vue'));
Vue.component('contribution-select', require('./components/contribution/SelectContributions.vue'));
Vue.component('buttons-print-contributions', require('./components/contribution/ButtonsPrintContributions.vue'));

//aid-contributions
Vue.component('aid-contribution-create', require('./components/contribution/CreateAidContribution.vue'));
Vue.component('contribution-aid-commitment',require('./components/contribution/AidCommitment.vue'));

// utils
Vue.component('sweet-alert-modal', require('./components/utils/SweetAlertModal.vue'));


const app = new Vue({
  el: '#app',
	store
    
});