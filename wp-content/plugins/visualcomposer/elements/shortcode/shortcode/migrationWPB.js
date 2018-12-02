import { getStorage, getService } from 'vc-cake'

const cook = getService('cook')
const migrationStorage = getStorage('migration')

// Migrate All not migrated elements to 'textBlock Element' Or to 'shortcode Element'
migrationStorage.on('migrateElement', (elementData) => {
  window.setTimeout(() => {
    if (!elementData._migrated) {
      const shortcodeElementAttributes = Object.assign({}, elementData._generalElementAttributes, { tag: 'shortcode', parent: elementData._parent, shortcode: elementData._shortcodeString })
      const shortcodeElement = cook.get(shortcodeElementAttributes)
      migrationStorage.trigger('add', shortcodeElement.toJS())
      elementData._migrated = true
    }
  }, 10)
})
