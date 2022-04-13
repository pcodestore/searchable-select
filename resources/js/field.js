import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('index-searchable-select', IndexField)
  app.component('detail-searchable-select', DetailField)
  app.component('form-searchable-select', FormField)
})
