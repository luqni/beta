import { getStorage, getService } from 'vc-cake'

const cook = getService('cook')
const migrationStorage = getStorage('migration')

migrationStorage.on('migrateElement', (elementData) => {
  if (elementData.tag === 'vc_column_text') {
    let baseAttrs = elementData._generalElementAttributes
    baseAttrs.output = elementData._subInnerContent
    const attrs = Object.assign({}, baseAttrs, { tag: 'textBlock' })
    const textBlockElement = cook.get(attrs)
    migrationStorage.trigger('add', textBlockElement.toJS())
    elementData._migrated = true
  }
})
