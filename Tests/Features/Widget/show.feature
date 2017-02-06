@mink:selenium2 @alice(Page) @reset-schema
Feature: Show a widget

    Background:
        Given the following Jedis:
            | name   | side   | midiChlorians | slug   |
            | Anakin | dark   | 20000         | anakin |
            | Yoda   | bright | 17500         | yoda   |


    Scenario: Error
        Given the following BusinessTemplate:
            | currentLocale |name                       | backendName  | slug                    |  businessEntityId | parent  | template | orderBy      |
            | fr            |Fiche Jedi - {{item.name}} | Fiche Jedi  | fiche-jedi-{{item.slug}} |  jedi             | home    | base     | [{"a": "b"}] |
