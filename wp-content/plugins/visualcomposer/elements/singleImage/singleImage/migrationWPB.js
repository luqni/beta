import { getStorage, getService } from 'vc-cake'

const cook = getService('cook')
const migrationStorage = getStorage('migration')

const parseSingleImageAttributes = (generalAttrs, shortcodeAttrs) => {
  const data = Object.assign({ size: 'thumbnail', alignment: 'left' }, generalAttrs, { tag: 'singleImage' })
  const imageFullUrl = window.VCV_API_WPBAKERY_WPB_MEDIA ? window.VCV_API_WPBAKERY_WPB_MEDIA() : []

  let imageId = parseInt(shortcodeAttrs.image)
  if (shortcodeAttrs.source === 'media_library' && imageId) {
    data.image = {
      id: imageId,
      full: imageFullUrl[ imageId ]
    }
  } else if (shortcodeAttrs.source === 'featured_image') {
    imageId = imageFullUrl[ 'featuredImageId' ]
    data.image = {
      id: imageId,
      full: imageFullUrl[ 'featuredImage' ]
    }
  } else if (shortcodeAttrs.source === 'external_link') {
    data.image = {
      full: shortcodeAttrs.custom_src,
      ids: [],
      urls: [ shortcodeAttrs.custom_src ]
    }
  }

  if (shortcodeAttrs.img_size) {
    data.size = shortcodeAttrs.img_size
  }
  if (shortcodeAttrs.add_caption && shortcodeAttrs.add_caption === 'yes') {
    data.showCaption = true // NOT working on element
    data.image.caption = imageFullUrl[ data.image.id + '_caption' ]
  }
  if (shortcodeAttrs.alignment) {
    data.alignment = shortcodeAttrs.alignment
  }
  if (shortcodeAttrs.style.match('rounded')) {
    data.shape = 'rounded'
  } else if (shortcodeAttrs.style.match('circle|round')) {
    data.shape = 'round'
  }
  if (shortcodeAttrs.onclick === 'img_link_large') {
    data.clickableOptions = 'imageNewTab'
  } else if (shortcodeAttrs.onclick === 'link_image') {
    data.clickableOptions = 'lightbox'
  } else if (shortcodeAttrs.onclick === 'zoom') {
    data.clickableOptions = 'zoom'
  } else if (shortcodeAttrs.onclick === 'custom_link') {
    // [vc_single_image onclick="custom_link" img_link_target="_blank" link="http://test1.com"]
    // default link target img_link_target='_self'
    data.clickableOptions = 'url'
    data.image.link = {
      relNofollow: false,
      targetBlank: shortcodeAttrs.img_link_target === '_blank',
      title: '',
      url: shortcodeAttrs.link
    }
  }

  return data
}

migrationStorage.on('migrateElement', (elementData) => {
  if (elementData.tag === 'vc_single_image') {
    const shortcodeAttrs = Object.assign({
      title: '',
      source: 'media_library',
      image: '',
      img_size: 'thumbnail',
      external_img_size: '',
      add_caption: '',
      alignment: 'left',
      style: '',
      external_style: '',
      border_color: 'grey',
      external_border_color: 'grey',
      onclick: '',
      img_link_target: '_self',
      css_animation: ''
    }, elementData._attrs)

    const attrs = Object.assign({}, parseSingleImageAttributes(elementData._generalElementAttributes, shortcodeAttrs))
    const cookElement = cook.get(attrs)
    migrationStorage.trigger('add', cookElement.toJS())
    elementData._migrated = true
  }
})
