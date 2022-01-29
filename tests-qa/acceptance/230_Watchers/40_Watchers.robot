*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../functions/Watch.robot
Resource        ../functions/PageMoves.robot
Resource        ../vars/users.resource
Resource        ../vars/geokrety.resource
Resource        ../vars/moves.resource
Force Tags      Watch    Access
Test Setup      Seed


*** Test Cases ***

List Is Empty
    Go To Url With Param                            ${PAGE_GEOKRETY_WATCHERS_URL}           gkid=${GEOKRETY_1.id}
    Page Should Contain                             No users are watching GeoKret ${GEOKRETY_1.name}

Watchers appear in watchers list
    Sign In ${USER_2.name} Fast
    Watch GeoKret                                   ${GEOKRETY_1.id}

    Go To Url With Param                            ${PAGE_GEOKRETY_WATCHERS_URL}           gkid=${GEOKRETY_1.id}
    Page Should Not Contain                         No users are watching GeoKret ${GEOKRETY_1.name}
    Element Count Should Be                         ${USER_WATCHERS_TABLE}/tbody/tr         1
    Table Cell Should Contain                       ${USER_WATCHERS_TABLE}    ${1 + 1}    1    ${USER_2.name}

    Sign In ${USER_3.name} Fast
    Watch GeoKret                                   ${GEOKRETY_1.id}

    Go To Url With Param                            ${PAGE_GEOKRETY_WATCHERS_URL}           gkid=${GEOKRETY_1.id}
    Element Count Should Be                         ${USER_WATCHERS_TABLE}/tbody/tr         2
    Table Cell Should Contain                       ${USER_WATCHERS_TABLE}    ${1 + 1}    1    ${USER_2.name}
    Table Cell Should Contain                       ${USER_WATCHERS_TABLE}    ${2 + 1}    1    ${USER_3.name}

Watchers Counter On GeoKret Page
    Sign In ${USER_2.name} Fast

    Go To Url With Param                            ${PAGE_GEOKRETY_DETAILS_URL}            gkid=${GEOKRETY_1.id}
    Page Should Contain Element                     ${GEOKRET_DETAILS_WATCHERS_LINK}
    Page should contain Element                     ${GEOKRET_DETAILS_WATCHERS_COUNT_BADGE}\[text()='0']

    Watch GeoKret                                   ${GEOKRETY_1.id}

    Go To Url With Param                            ${PAGE_GEOKRETY_DETAILS_URL}            gkid=${GEOKRETY_1.id}
    Page Should Contain Element                     ${GEOKRET_DETAILS_WATCHERS_LINK}
    Page should contain Element                     ${GEOKRET_DETAILS_WATCHERS_COUNT_BADGE}\[text()='1']

    Sign In ${USER_3.name} Fast
    Watch GeoKret                                   ${GEOKRETY_1.id}

    Go To Url With Param                            ${PAGE_GEOKRETY_DETAILS_URL}            gkid=${GEOKRETY_1.id}
    Page Should Contain Element                     ${GEOKRET_DETAILS_WATCHERS_LINK}
    Page should contain Element                     ${GEOKRET_DETAILS_WATCHERS_COUNT_BADGE}\[text()='2']


*** Keywords ***

Seed
    Clear DB And Seed 3 users
    Seed 2 geokrety owned by ${USER_1.id}
