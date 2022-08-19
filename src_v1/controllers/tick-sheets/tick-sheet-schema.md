# TickSheet schema

Tick Sheets are structured as JSON documents. This allows users to create structured tick sheets with nesting and ordering etc. Eventually, it will be possible to export a tick sheet as a data file and share it with other users of the membership software (from other clubs).

The initial format only supports checkboxes, with radios, selects and ranges planned in future. The addition of these will not be breaking changes.

## JSON format

A TickSheet document consists of a `ticksheet` root node.

The TickSheet node contains a `Group` object.

e.g.

```json
{
  "ticksheet": {
    "id": "93772239-51b2-4f1a-9c02-34f054d84785",
    "type": "Group",
    "name": "Gold Award",
    "children": [
      ...
    ]
  }
}
```

## Objects

For any object with a `type` field, this is mandatory.

### `Group`

A `Group` is a `Component` which stores a list of children (which are `Component`s of any type) and has an optional title.

Field         | Type                   | Description
--------------|------------------------|------------------------------------------
type          | string                 | describes object type
uuid          | string                 | Unique ID of Group 
name          | string                 | group name (displayed to user), optional
children      | array                  | list of child nodes/objects

e.g.

```json
{
  "id": "4704baa8-2319-4d36-afe1-e13db3c05fc4",
  "type": "Group",
  "name": "Element 1 (Competitions)",
  "children": [
    ...
  ]
}
```

### `CheckboxGroup`

A `CheckboxGroup` is a `Component` for a HTML checkboxes. Its children are `Checkbox` objects, allowing one or more items to be checked for each item.

Field         | Type                   | Description
--------------|------------------------|------------------------------------------
type          | string                 | describes object type
uuid          | string                 | Unique ID of CheckboxGroup
name          | string                 | label displayed to user
required      | boolean                | whether one or more of this group must be checked
checkboxes    | array                  | list of child nodes/objects

e.g.

```json
{
  "id": "0dc88cdf-7d5f-40e6-b95a-d4124ba4d9b6",
  "type": "CheckboxGroup",
  "name": "Element 1 (Competitions)",
  "checkboxes": [
    ...
  ]
}
```

### `Checkbox`

A `Checkbox` is a `Component` for a HTML checkboxes. It has no children and inherits properties, such as required from its parents.

Field         | Type                   | Description
--------------|------------------------|------------------------------------------
type          | string                 | Describes object type
uuid          | string                 | Unique ID of checkbox 
name          | string                 | Label displayed to user
value         | string                 | Checkbox value (optional, default `"1"`)
checked       | boolean                | Checked by default (optional, default `false`)

e.g.

```json
{
  "id": "912d49d6-1599-498e-a1c1-6f188874d9a5",
  "type": "Checkbox",
  "name": "External Meet 1",
  "value": "1",
  "checked": false
}
```