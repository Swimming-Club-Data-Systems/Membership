# Custom Form Fields

You can add custom form fields to the competition entry process for *guest entries*.

You can add fields to the
* entry header
* entrant details

Fields to be added to a page are described using JSON.

## Basic JSON format

The basic description format is as follows;

```json
{
  "header": [
    
  ],
  "entrants": [
    
  ]
}
```

Fields are displayed in the order they are defined in the respective header and entrants arrays.

## Available fields

The following field types are available;

### Textbox

For strings of up to 255 characters.

| Property | Required | Description                          |
|----------|---------|--------------------------------------|
| `name`   |✅| Name of the field. Must be unique.   |
| `label`  |✅| Field label.                         |
| `help`   || Help text associated with the field. |

### Textarea

For longer strings with multiple lines allowed.

| Property | Required | Description                          |
|----------|---------|--------------------------------------|
| `name`   |✅| Name of the field. Must be unique.   |
| `label`  |✅| Field label.                         |
| `help`   || Help text associated with the field. |

### Number

| Property    | Required | Description                          |
|-------------|---------|--------------------------------------|
| `name`      |✅| Name of the field. Must be unique.   |
| `label`     |✅| Field label.                         |
| `help`      || Help text associated with the field. |
| `precision` |✅| Number of decimal places to support. |

### Datetime

| Property | Required | Description                                       |
|------|------|---------------------------------------------------|
| `name` |✅| Name of the field. Must be unique.                |
| `label` |✅| Field label.                                      |
| `help` || Help text associated with the field.              |
| `min` || Minimum datetime.                                 |
| `max` || Maximum datetime.                                 |
| `showTimeInput`   || Whether to show the time picker. Default `false`. |

### Select

| Property | Required | Description                          |
|----------|----------|--------------------------------------|
| `name`   | ✅        | Name of the field. Must be unique.   |
| `label`  | ✅        | Field label.                         |
| `help`   |          | Help text associated with the field. |
| `values` | ✅        | Array of value/name pairs.           |

The values array looks like;

```js
[
    { value: "draft", name: "Draft" },
    { value: "published", name: "Published" },
    { value: "paused", name: "Paused" },
    { value: "closed", name: "Closed" },
    { value: "cancelled", name: "Cancelled" },
]
```

### Checkbox

| Property | Required | Description                          |
|----------|----------|--------------------------------------|
| `name`   | ✅        | Name of the field. Must be unique.   |
| `label`  | ✅        | Field label.                         |

### Radio

| Property | Required | Description                          |
|----------|----------|--------------------------------------|
| `name`   | ✅        | Name of the field. Must be unique.   |
| `label`  | ✅        | Field label.                         |
| `values` | ✅        | Array of value/name pairs.           |

### `<p>`

| Property | Required | Description      |
|----------|----------|------------------|
| `value`  | ✅        | Text to display. |

Paragraph of text.