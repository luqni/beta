import { getStorage, getService } from 'vc-cake'

const cook = getService('cook')
const migrationStorage = getStorage('migration')

const defaultColumnGap = 30
let rowDefaultSettings = {}

const parseRowAttributes = (generalAttrs, shortcodeAttrs) => {
  const data = Object.assign({}, {
    designOptionsAdvanced: { device: { all: {} } }
  }, generalAttrs, { tag: 'row', rowWidth: 'boxed' })
  shortcodeAttrs = Object.assign(
    {
      full_width: '',
      gap: '0',
      full_height: '',
      columns_placement: '',
      equal_height: '',
      content_placement: '',
      video_bg: '',
      video_bg_url: 'https://www.youtube.com/watch?v=lMJXxhRFO1k',
      video_bg_parallax: '',
      parallax: '',
      parallax_image: '',
      parallax_speed_video: '1.5',
      parallax_speed_bg: '1.5',
      css_animation: '',
      disable_element: ''
    }, shortcodeAttrs)
  if (shortcodeAttrs.full_width === 'stretch_row') {
    data.rowWidth = 'stretchedRow'
  } else if (shortcodeAttrs.full_width === 'stretch_row_content') {
    data.rowWidth = 'stretchedRowAndColumn'
  } else if (shortcodeAttrs.full_width === 'stretch_row_content_no_spaces') {
    data.rowWidth = 'stretchedRowAndColumn'
    data.removeSpaces = true
  }
  if (shortcodeAttrs.full_height) {
    data.fullHeight = true

    if (shortcodeAttrs.columns_placement) {
      data.columnPosition = shortcodeAttrs.columns_placement
    } else { // Wpbakery default is middle
      data.columnPosition = 'middle'
    }
  }
  if (shortcodeAttrs.gap) {
    data.columnGap = `${parseInt(shortcodeAttrs.gap) + defaultColumnGap}`
  }
  if (shortcodeAttrs.equal_height) {
    data.equalHeight = true
  }
  if (shortcodeAttrs.content_placement) {
    data.contentPosition = shortcodeAttrs.content_placement

    if (shortcodeAttrs.content_placement === 'middle') {
      data.equalHeight = true
    } else if (shortcodeAttrs.content_placement === 'bottom') {
      data.equalHeight = true
    }
  }
  if (shortcodeAttrs.disable_element) {
    data.hidden = true
  }

  // Design Options Advanced
  if (shortcodeAttrs.video_bg) {
    data.designOptionsAdvanced.device.all.backgroundType = 'videoYoutube'
    data.designOptionsAdvanced.device.all.videoYoutube = shortcodeAttrs.video_bg_url ? shortcodeAttrs.video_bg_url : rowDefaultSettings.video_bg_url
  }

  // Design Options Advanced
  if (shortcodeAttrs.parallax) {
    if (shortcodeAttrs.parallax === 'content-moving') {
      data.designOptionsAdvanced.device.all.parallax = 'simple'
    } else if (shortcodeAttrs.parallax === 'content-moving-fade') {
      data.designOptionsAdvanced.device.all.parallax = 'simple-fade'
    }

    const parallaxSpeed = shortcodeAttrs.parallax_speed_bg || rowDefaultSettings.parallax_speed_bg || '1.5'
    data.designOptionsAdvanced.device.all.parallaxSpeed = parseFloat(parallaxSpeed) * 6
    data.designOptionsAdvanced.device.all.backgroundType = 'imagesSimple'

    const imageId = parseInt(shortcodeAttrs.parallax_image)
    const imageFullUrl = window.VCV_API_WPBAKERY_WPB_MEDIA && window.VCV_API_WPBAKERY_WPB_MEDIA()[ shortcodeAttrs.parallax_image ]
    const designOptionsData = data.designOptionsAdvanced.device.all
    if (imageId && imageFullUrl) {
      if (designOptionsData.images && designOptionsData.images.hasOwnProperty('ids')) {
        data.designOptionsAdvanced.device.all.images.ids.push(imageId)
        data.designOptionsAdvanced.device.all.images.urls.push({ full: imageFullUrl, id: imageId })
      } else {
        data.designOptionsAdvanced.device.all.images = {
          ids: [ imageId ],
          urls: [ { full: imageFullUrl, id: imageId } ]
        }
      }
      if (!designOptionsData.backgroundStyle) {
        designOptionsData.backgroundStyle = 'cover'
      }
    }
  }

  return data
}

migrationStorage.on('migrateElement', (elementData) => {
  if (elementData.tag === 'vc_row' || elementData.tag === 'vc_row_inner') {
    const rowAttributes = Object.assign({}, parseRowAttributes(elementData._generalElementAttributes, elementData._attrs))
    const rowElement = cook.get(rowAttributes)
    migrationStorage.trigger('add', rowElement.toJS(), false)
    if (elementData._subInnerContent) {
      elementData._parse(elementData._multipleShortcodesRegex, elementData._subInnerContent, rowElement.get('id'))
    }
    elementData._migrated = true
  }
})
