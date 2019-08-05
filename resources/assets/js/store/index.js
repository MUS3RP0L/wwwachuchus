import Vue from 'vue';
import Vuex from 'vuex';
 Vue.use(Vuex); 

import retFunForm from './modules/retFun/form'
import quotaAidForm from './modules/quotaAid/form'
import inbox from './modules/inbox'
import directContributionForm from './modules/directContribution/form'
import contributionProcessForm from './modules/contributionProcess/form'
import ecoComForm from './modules/ecoCom/form'

export default new Vuex.Store({
  modules: {
    retFunForm: retFunForm,
    inbox: inbox,
    quotaAidForm, //quotaAidForm: quotaAidForm
    directContributionForm,
    contributionProcessForm,
    ecoComForm,
  }
});