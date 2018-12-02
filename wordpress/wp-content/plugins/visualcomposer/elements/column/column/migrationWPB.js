import { getStorage, getService } from 'vc-cake'

const cook = getService('cook')
const migrationStorage = getStorage('migration')

migrationStorage.on('migrateElement', (elementData) => {
  if (elementData.tag === 'vc_column' || elementData.tag === 'vc_column_inner') {
    let rowElement = migrationStorage.state('elements').get()[elementData._parent]
    const columnAttributes = Object.assign({}, parseColumnAttributes(elementData._generalElementAttributes, elementData._attrs, elementData._parent, rowElement))

    if (elementData._attrs.offset && columnAttributes.size && columnAttributes.size.hasOwnProperty('xs')) {
      if (!rowElement.hasOwnProperty('layout')) {
        rowElement.layout = {
          responsivenessSettings: true
        }
      } else if (!rowElement.layout.responsivenessSettings) {
        rowElement.layout.responsivenessSettings = true
      }

      migrationStorage.trigger('update', rowElement.id, rowElement)
    }

    const offsetData = getOffset(elementData._attrs.offset)

    if (offsetData) {
      const columnOffsetElement = cook.get({ tag: 'column', parent: elementData._parent, size: { defaultSize: 'hide', ...offsetData } })
      migrationStorage.trigger('add', columnOffsetElement.toJS(), false)
    }

    const columnElement = cook.get(columnAttributes)
    migrationStorage.trigger('add', columnElement.toJS(), false, { action: 'merge' })
    if (elementData._subInnerContent) {
      elementData._parse(elementData._multipleShortcodesRegex, elementData._subInnerContent, columnElement.get('id'))
    }
    elementData._migrated = true
  }
})

const parseColumnAttributes = (generalAttrs, attrs, parent, rowElement) => {
  const defaultWidth = attrs.width || '100%'
  const data = Object.assign({}, generalAttrs, { tag: 'column', parent: parent, size: { all: defaultWidth, defaultSize: defaultWidth } })

  if (attrs.offset) {
    const deviceSizes = getDeviceSizes(attrs.offset, defaultWidth)
    data.size = { defaultSize: defaultWidth, ...deviceSizes }
  } else if (rowElement.layout && rowElement.layout.responsivenessSettings) {
    data.size = { defaultSize: defaultWidth, xs: '100%', sm: '100%', md: defaultWidth, lg: defaultWidth, xl: defaultWidth }
  } else {
    data.size = { all: defaultWidth, defaultSize: defaultWidth }
  }

  return data
}

const migrationSizes = { xs: 'xs', sm: 'xs', md: 'sm', lg: 'md', xl: 'lg' }
const defaultDevices = [ 'xs', 'sm', 'md', 'lg', 'xl' ]

const getDeviceSizes = (stringData, defaultWidth) => {
  const deviceSizes = {}
  const arrayData = stringData.split(' ')

  const sizeArray = arrayData.filter((item) => {
    const isHidden = item.indexOf('hidden') > -1
    const isOffset = item.indexOf('offset') > -1
    return !isHidden && !isOffset
  })

  const hiddenArray = arrayData.filter((item) => {
    return item.indexOf('hidden') > -1
  })

  sizeArray.forEach((deviceSize) => {
    defaultDevices.forEach((deviceKey) => {
      if (deviceSize.indexOf(deviceKey) > -1) {
        let size = deviceSize.split(`${deviceKey}-`)[ 1 ]
        if (size.indexOf('/') < 0) {
          size = `${size}/12`
        }
        Object.keys(migrationSizes).forEach((migratedDeviceKey) => {
          if (migrationSizes[ migratedDeviceKey ] === deviceKey) {
            deviceSizes[ migratedDeviceKey ] = reduceFraction(size)
          }
        })
      }
    })
  })

  if (hiddenArray.length) {
    hiddenArray.forEach((hideString) => {
      defaultDevices.forEach((deviceKey) => {
        if (hideString.indexOf(deviceKey) > -1) {
          Object.keys(migrationSizes).forEach((migratedDeviceKey) => {
            if (migrationSizes[ migratedDeviceKey ] === deviceKey) {
              deviceSizes[ migratedDeviceKey ] = 'hide'
            }
          })
        }
      })
    })
  }

  let inheritedOffset = null
  defaultDevices.forEach((deviceKey) => {
    let deviceValue = deviceSizes[ deviceKey ]
    let defaultDeviceSize = (deviceKey === 'xs' || deviceKey === 'sm') ? '100%' : defaultWidth

    if (!deviceValue) {
      deviceSizes[ deviceKey ] = inheritedOffset || defaultDeviceSize
    }

    if (!deviceValue && deviceValue !== 'hide' && deviceSizes[ deviceKey ] !== '100%') {
      inheritedOffset = deviceSizes[ deviceKey ]
    }
  })

  return deviceSizes
}

const reduceFraction = (fractionString) => {
  const fraction = fractionString.split('/')
  const numerator = fraction[ 0 ]
  const denominator = fraction[ 1 ]
  const gcd = (numerator, denominator) => {
    return numerator % denominator === 0 ? denominator : gcd(denominator, numerator % denominator)
  }
  const divideBy = gcd(numerator, denominator)
  return `${numerator / divideBy}/${denominator / divideBy}`
}

const getOffset = (offsetData) => {
  if (!offsetData) {
    return null
  }
  const arrayData = offsetData.split(' ')
  const offsetArray = arrayData.filter((item) => {
    return item.indexOf('offset') > -1
  })

  if (!offsetArray.length) {
    return null
  }

  const deviceSizes = {}

  offsetArray.forEach((offsetString) => {
    defaultDevices.forEach((deviceKey) => {
      if (offsetString.indexOf(deviceKey) > -1) {
        let size = offsetString.split(`offset-`)[ 1 ]
        if (size.indexOf('/') < 0 && size !== '0') {
          size = `${size}/12`
        }
        Object.keys(migrationSizes).forEach((migratedDeviceKey) => {
          if (migrationSizes[ migratedDeviceKey ] === deviceKey) {
            deviceSizes[ migratedDeviceKey ] = size === '0' ? '0' : reduceFraction(size)
          }
        })
      }
    })
  })

  let inheritedOffset = null
  defaultDevices.forEach((deviceKey) => {
    let offsetValue = deviceSizes[ deviceKey ]

    if (!offsetValue) {
      deviceSizes[ deviceKey ] = inheritedOffset || 'hide'
    } else if (offsetValue === '0') {
      deviceSizes[ deviceKey ] = 'hide'
    }
    inheritedOffset = deviceSizes[ deviceKey ]
  })

  return deviceSizes
}
