*** Settings ***
Library         RequestsLibrary
Resource        ../ressources/Authentication.robot
Resource        ../ressources/Geokrety.robot
Variables       ../ressources/vars/users.yml
Variables       ../ressources/vars/geokrety.yml
Test Setup      Test Setup

*** Test Cases ***


Anonymous cannot access form
    Sign Out Fast
    Go To Url                               ${PAGE_GEOKRETY_EDIT_URL}    redirect=${PAGE_SIGN_IN_URL}
    Flash message shown                     ${UNAUTHORIZED}
    Page Should Contain                     ${UNAUTHORIZED}

Owner can access form
    Sign In ${USER_1.name} Fast
    Go To Url                               ${PAGE_GEOKRETY_EDIT_URL}
    Page Should Contain                     GeoKret label preview

Cannot edit someone else GeoKret
    Sign In ${USER_2.name} Fast
    Go To Url                               ${PAGE_GEOKRETY_EDIT_URL}    redirect=${PAGE_GEOKRETY_1_DETAILS_URL}
    Flash message shown                     Only the owner can edit his GeoKrety
    Page Should Not Contain                 GeoKret label preview


Edit A GeoKret
    Sign In ${USER_1.name} Fast
    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    ${selected_template} =    Get Selected List Value    ${GEOKRET_CREATE_LABEL_TEMPLATE_SELECT}
    Should Be Equal                     ${selected_template}                default

    Input Text                          ${GEOKRET_CREATE_NAME_INPUT}        GKNewName
    Select From List By Value           ${GEOKRET_CREATE_TYPE_SELECT}       1
    Input Inscrybmde                    \#inputMission                      New mission
    Select From List By Value           ${GEOKRET_CREATE_LABEL_TEMPLATE_SELECT}       sansanchoz1

    Click Button                        ${GEOKRET_CREATE_CREATE_BUTTON}
    Location Should Be                  ${PAGE_GEOKRETY_1_DETAILS_URL}
    Element Should Contain              ${GEOKRET_DETAILS_NAME}             GKNewName
    Element Should Contain              ${GEOKRET_DETAILS_TYPE}             A book/CD/DVD…
    Element Should Contain              ${GEOKRET_DETAILS_MISSION}          New mission

    Go To Url                           ${PAGE_GEOKRETY_EDIT_URL}
    ${selected_template} =    Get Selected List Value    ${GEOKRET_CREATE_LABEL_TEMPLATE_SELECT}
    Should Be Equal                     ${selected_template}                sansanchoz1


*** Keywords ***

Test Setup
    Clear Database And Seed ${2} users
    Seed ${1} geokrety owned by ${1}
