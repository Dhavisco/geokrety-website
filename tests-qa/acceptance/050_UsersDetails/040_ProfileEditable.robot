*** Settings ***
Resource        ../functions/FunctionsGlobal.robot
Resource        ../vars/users.resource
Force Tags      Users Details
Suite Setup     Seed

*** Test Cases ***

Buttons shown for anonymous users
    Sign Out Fast
    Go To User 1 url
    Check Public Elements Absence
    Check Private Elements Absence

Buttons shown for authenticated users
    Sign In ${USER_2.name} Fast
    Go To User 1 url
    Page Should Contain Element         ${USER_PROFILE_CONTACT_BUTTON}
    Check Private Elements Absence

Buttons shown for authenticated users - himself
    Sign In ${USER_1.name} Fast
    Go To User 1 url
    Check Private Elements Presence
    Page Should Not Contain Element     ${USER_PROFILE_CONTACT_BUTTON}


*** Keywords ***

Seed
    Clear Database
    Seed 2 users

Check Public Elements Absence
    Page Should Not Contain Element     ${USER_PROFILE_CONTACT_BUTTON}
    Page Should Not Contain Element     ${USER_PROFILE_AUTHENTICATION_HISTORY_BUTTON}
    ${count} =              Get Element Count       name:div_name
    Should Be True          ${count} == ${0}

Check Private Elements Absence
    Page Should Not Contain Element     ${USER_PROFILE_LANGUAGE_EDIT_BUTTON}
    Page Should Not Contain Element     ${USER_PROFILE_EMAIL_EDIT_BUTTON}
    Page Should Not Contain Element     ${USER_PROFILE_SECID_REFRESH_BUTTON}
    Page Should Not Contain Element     ${USER_PROFILE_PICTURE_UPLOAD_BUTTON}
    Page Should Not Contain Element     ${USER_PROFILE_BANNER_EDIT_BUTTON}
    Page Should Not Contain Element     ${USER_PROFILE_HOME_POSITION_EDIT_BUTTON}
    Page Should Not Contain Element     ${USER_PROFILE_HOME_POSITION_EDIT_BUTTON_MINIMAP}

Check Private Elements Presence
    Page Should Contain Element         ${USER_PROFILE_LANGUAGE_EDIT_BUTTON}
    Page Should Contain Element         ${USER_PROFILE_EMAIL_EDIT_BUTTON}
    Page Should Contain Element         ${USER_PROFILE_SECID_REFRESH_BUTTON}
    Page Should Contain Element         ${USER_PROFILE_PICTURE_UPLOAD_BUTTON}
    Page Should Contain Element         ${USER_PROFILE_BANNER_EDIT_BUTTON}
    Page Should Contain Element         ${USER_PROFILE_HOME_POSITION_EDIT_BUTTON}
    Page Should Contain Element         ${USER_PROFILE_HOME_POSITION_EDIT_BUTTON_MINIMAP}
