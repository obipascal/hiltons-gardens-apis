@extends('email.includes.emailLayout')
@section('emailContent')
<table class="es-content" cellspacing="0" cellpadding="0" align="center" role="none"
    style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%">
    <tr>
        <td align="center" bgcolor="#efefef" style="padding:0;Margin:0;background-color:#efefef">
            <table class="es-content-body" cellspacing="0" cellpadding="0" bgcolor="#ffffff" align="center" role="none"
                style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#FFFFFF;width:600px">
                <tr>
                    <td align="left" style="padding:15px;Margin:0">
                        <table cellpadding="0" cellspacing="0" width="100%" role="none"
                            style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                            <tr>
                                <td align="center" valign="top" style="padding:0;Margin:0;width:570px">
                                    <table cellpadding="0" cellspacing="0" width="100%" role="presentation"
                                        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                        <tr>
                                            <td align="center" style="padding:10px;Margin:0">
                                                <h3
                                                    style="Margin:0;line-height:24px;mso-line-height-rule:exactly;font-family:tahoma, verdana, segoe, sans-serif;font-size:20px;font-style:normal;font-weight:normal;color:#333333">
                                                    <strong>
                                                        {{ $title ?? "Verify your account" }}
                                                    </strong>
                                                </h3>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left" style="padding:10px;Margin:0">
                                                <p
                                                    style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333;font-size:14px">
                                                    Please to continue with your transaction click the button below to
                                                    open the Hilton's Garden app.
                                                </p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" class="es-m-txt-c" style="padding:10px;Margin:0">
                                                <h1
                                                    style="Margin:0;line-height:58px;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-size:48px;font-style:normal;font-weight:normal;color:#333333">
                                                    <a href="hiltons://com.hiltonsgarden.com/?reference={{ $reference }}&status={{ $status }}" class="es-button cta-btn"
                                                        role="button">
                                                        <strong>Back to App</strong>
                                                    </a>
                                                    <p
                                                        style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333;font-size:14px">
                                                        <strong>Reference: {{ $reference ?? "" }}</strong>
                                                    </p>
                                                </h1>

                                            </td>

                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<table cellpadding="0" cellspacing="0" class="es-content" align="center" role="none"
    style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%">
    <tr>
        <td class="es-info-area" align="center" bgcolor="#efefef" style="padding:0;Margin:0;background-color:#efefef">
            <table class="es-content-body"
                style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#ffffff;width:600px"
                cellspacing="0" cellpadding="0" bgcolor="#ffffff" align="center" role="none">
                <tr>
                    <td style="padding:0;Margin:0;padding-top:5px;background-color:#ffffff" bgcolor="#ffffff"
                        align="left">
                        <table width="100%" cellspacing="0" cellpadding="0" role="none"
                            style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                            <tr>
                                <td valign="top" align="center" style="padding:0;Margin:0;width:600px">
                                    <table width="100%" cellspacing="0" cellpadding="0" role="presentation"
                                        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                        <tr>
                                            <td align="center" class="es-infoblock"
                                                style="padding:0;Margin:0;line-height:120%;font-size:0;color:#CCCCCC">
                                                <table border="0" width="100%" height="100%" cellpadding="0"
                                                    cellspacing="0" role="presentation"
                                                    style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                    <tr>
                                                        <td
                                                            style="padding:0;Margin:0;border-bottom:1px solid #cccccc;background:unset;height:1px;width:100%;margin:0px">
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0;Margin:0;padding-left:5px;padding-right:5px;padding-bottom:10px;background-color:#ffffff"
                        bgcolor="#ffffff" align="left">
                        <table width="100%" cellspacing="0" cellpadding="0" role="none"
                            style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                            <tr>
                                <td valign="top" align="center" style="padding:0;Margin:0;width:590px">
                                    <table
                                        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-position:center top"
                                        width="100%" cellspacing="0" cellpadding="0" role="presentation">
                                        <tr>
                                            <td align="center" class="es-infoblock"
                                                style="padding:15px;Margin:0;line-height:19px;font-size:12px;color:#CCCCCC">
                                                <p
                                                    style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:19px;color:#ff6600;font-size:16px">
                                                    <em>We will never email you and ask you to disclose or verify your
                                                        password, credit card, or banking account number.</em>
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>


@endsection
