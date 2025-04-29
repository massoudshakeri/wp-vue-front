import { createApp } from 'vue'
import SuppliersView from './components/SuppliersView.vue';
import AccountingView from './components/AccountingView.vue';
import TeamView from './components/TeamView.vue';
import ProductsView from './components/ProductsView.vue';

const componentMap = {
  'accounting-view': AccountingView,
  'suppliers-view': SuppliersView,
  'team-view': TeamView,
  'products-view': ProductsView,
  // add more components here, keyed by the data-vue-component value
}
function mountVueComponents() {
  
  document.querySelectorAll('[data-vue-component]').forEach(el => {
    if (el.__vue_mounted) return

    const name = el.dataset.vueComponent
    let props = {}
  
    try {
      props = el.dataset.vueProps ? JSON.parse(el.dataset.vueProps) : {}
    } catch (err) {
      console.error('Failed to parse props for', name, 'on element', el, err)
    }
  
    console.log('Mounting', name, 'with props', props)
    // if (el.__vue_mounted) return // ✅ skip if already mounted
    // const name = el.dataset.vueComponent
    // const props = JSON.parse(el.dataset.vueProps || '{}')
    // console.log(JSON.parse(el.dataset.vueComponent))
    if (componentMap[name] && !el.__vue_mounted) {
      const app = createApp(componentMap[name], props)
      app.mount(el)
      el.__vue_mounted = true // ✅ mark as mounted
    }
  })
}

// Run initially
mountVueComponents()

// Also watch for dynamic DOM insertions
const observer = new MutationObserver(() => {
  mountVueComponents()
})

observer.observe(document.body, { childList: true, subtree: true })
