# User Guide

## Image Options

- **src**: Specifies the path to the image.
  - Example: `src="images/example.jpg"`
- **caption**: Adds a caption below the image.
  - Example: `caption="My image caption"`
- **alt**: Provides alternative text for the image.
  - Example: `alt="My alt text"`
- **title**: Adds a title attribute to the image.
  - Example: `title="My title attribute"`

## Don’t Output Figure Tag

- **figure**: Controls whether a `<figure>` tag is used.
  - Example: `figure="off"`

## Crop to Aspect Ratio

- **ar**: Crops the image to a specific aspect ratio.
  - Example: `ar="16x9"`

## CSS Options

- **figure-class**: Adds custom classes to the `<figure>` tag.
  - Example: `figure-class="class-1 class-2"`
- **class**: Adds custom classes to the `<img>` tag.
  - Example: `class="class-3 class-4"`
- **style**: Adds custom inline styles to the `<img>` tag.
  - Example: `style="border: #000 solid 1px;"`

## Image Size Hints

- **container**: Defines the container type.
  - Example: `container="fluid"`
- **xs**: Specifies the size for extra small devices.
  - Example: `xs=12`
- **sm**: Specifies the size for small devices.
  - Example: `sm=12`
- **md**: Specifies the size for medium devices.
  - Example: `md=12`
- **lg**: Specifies the size for large devices.
  - Example: `lg=12`
- **xl**: Specifies the size for extra-large devices (Bootstrap 4+5 only).
  - Example: `xl=12`
- **xxl**: Specifies the size for extra-extra-large devices (Bootstrap 5 only).
  - Example: `xxl=12`

## Example Usage

Here is an example of using all the options together:

```html
{insertimage src="images/example.jpg" caption="My image caption" alt="My alt text" title="My title attribute" figure="off" ar="16x9" figure-class="class-1 class-2" class="class-3 class-4" style="border: #000 solid 1px;" container="fluid" xs=12 sm=12 md=12 lg=12 xl=12 xxl=12}
