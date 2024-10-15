<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Xulosa</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap');
    </style>

    <style>
        * {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 16px;
            color: #080B22;
        }

        .clearfix {
            content: "";
            clear: both;
            display: table;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            color: #080B22;
        }

        .custom-table * {
            font-size: 8px;
            color: #080B22;
        }

        @page {
            size: A4 landscape;
            margin: 10px;
        }
    </style>
</head>

<body>
<div style="padding: 10px;">
    <table style="margin-bottom: 30px; width:100%; border: none; border-collapse:collapse">
        <tbody>
        <tr>
            <td style="font-weight: 700; font-size: 22px; text-align: center; text-transform: uppercase;padding-bottom: 10px;">
                {{ $headName }}
            </td>
        </tr>
        <tr>
            <td>
                <div style="border-bottom: 1px solid #687196; border-top: 1px solid #687196;">
                </div>
            </td>
        </tr>

        </tbody>
    </table>

    <table style="margin-bottom: 30px; width:100%; border: none; border-collapse:collapse">
        <tbody>
        <tr>
            <td>
                <div style="font-weight: 600; margin-bottom: 12px; text-transform: uppercase; font-size: 20px;">
                    Qurilish-montaj ishlari tugallangan obyektdan foydalanish uchun <br> ruxsatnoma berish va kadastr
                    hujjatlarini rasmiylashtirish bo'yicha XULOSA:
                </div>
            </td>
        </tr>
        <tr>
            <td>
							<span style="font-style: italic;">{{ $review->answer }}</span>
            </td>
        </tr>
        </tbody>
    </table>

    <table style="margin-bottom: 10px; width:100%; border: none; border-collapse:collapse">
        <tbody>
        <tr>
            <td colspan="2" style="padding: 5px 0;">
                <span style="font-weight: 600;">Obyekt nomi:</span>
                <div>{{ $review->monitoring->claim->object->name }}</div>
            </td>
        </tr>
        <tr>
            <td style="width: 50%; padding: 10px 0;">
                <span style="font-weight: 600;">Obyekt manzili:</span>
                <div>{{ $review->monitoring->claim->object->location_building }}</div>
            </td>
            <td style="padding: 10px 0;">
                <span style="font-weight: 600;">Hudud:</span>
                <div>{{ $review->monitoring->claim->district()->first()->name_uz }}</div>
            </td>
        </tr>
        </tbody>
    </table>

    <table style="margin-bottom: 10px; width:100%; border: none; border-collapse:collapse">
        <tbody>
        <tr>
            <td style="width: 50%; padding: 5px 0;">
                <span style="font-weight: 600;">Obyektning ariza raqami:</span>
                <div>{{ $review->monitoring->claim->guid }}</div>
            </td>
            <td style="padding: 10px 0;">
                <span style="font-weight: 600;">Berilgan javob: </span>
                <div>{{ $review->status ? 'Qabul qilindi' : 'Rad qilingi'}}</div>
            </td>
        </tr>
        <tr>
            <td style="width: 50%; padding: 10px 0;">
                <span style="font-weight: 600;">Ariza kelgan vaqt:</span>
                <div>{{ $review->created_at }}</div>
            </td>
            <td style="padding: 5px 0;">
                <span style="font-weight: 600; ">Arizaga javob berilgan vaqt:</span>
                <div>{{ $review->answered_at }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding: 10px 0;">
                <span style="font-weight: 600;">Mas'ul shaxs: </span>
                <div>{{ $name }}</div>
            </td>
        </tr>
        </tbody>
    </table>

    <table style="margin-bottom: 10px; width:100%; border: none; border-collapse:collapse">
        <tbody>
        <tr>
            <td style="width: 50%; padding: 10px 0;">
                <img src='data:image/png;base64," . {{ $qrCode }} . "'>
            </td>
            <td style="vertical-align: bottom; text-align: right;">
                <img src="data:image/svg+xml;base64,{{ base64_encode('<svg width="267" height="40" viewBox="0 0 267 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_17_2)">
                        <path
                            d="M58.617 29.0605C59.7935 30.0402 61.2316 30.4972 63.0617 30.4972C64.6958 30.4972 65.9378 30.1707 66.9836 29.4525C67.964 28.734 68.487 27.7545 68.487 26.5135C68.487 25.5992 68.2255 24.8809 67.6373 24.3584C67.1143 23.836 66.3954 23.4441 65.5455 23.1829C64.6958 22.9217 63.5193 22.6605 62.0159 22.3992C59.6628 22.0074 57.8324 21.3543 56.3944 20.44C55.0217 19.5257 54.3028 18.0236 54.3028 15.9991C54.3028 14.693 54.6297 13.5174 55.2832 12.4725C55.937 11.4276 56.9174 10.6439 58.094 10.1214C59.2705 9.599 60.7086 9.27245 62.2774 9.27245C63.9116 9.27245 65.4147 9.599 66.6567 10.1868C67.8986 10.7745 68.9446 11.6235 69.6635 12.7337C70.3827 13.844 70.8401 15.0848 70.9055 16.4563H68.0293C67.8332 15.0848 67.2451 13.9746 66.1993 13.1909C65.1535 12.3419 63.8462 11.95 62.2774 11.95C60.7086 11.95 59.4666 12.2766 58.5516 12.995C57.6363 13.648 57.1789 14.6277 57.1789 15.8685C57.1789 16.7828 57.4404 17.4359 57.9632 17.9583C58.4862 18.4808 59.2051 18.8073 60.055 19.0686C60.9047 19.3298 62.0812 19.591 63.5193 19.8522C65.8724 20.2441 67.7681 20.8972 69.1405 21.8115C70.5786 22.7258 71.2324 24.2278 71.2324 26.2522C71.2324 27.5585 70.9055 28.7992 70.1866 29.8442C69.4674 30.8892 68.487 31.7382 67.2451 32.2607C66.0031 32.8485 64.5651 33.1097 62.9309 33.1097C61.1008 33.1097 59.532 32.783 58.1593 32.13C56.7867 31.477 55.7409 30.5627 55.0217 29.3217C54.3028 28.1462 53.8452 26.7747 53.7798 25.2075H56.6559C56.7867 26.8402 57.4404 28.081 58.617 29.0605Z"
                            fill="#1C1C1C"/>
                        <path
                            d="M89.9273 23.3734V32.9083H87.182V23.5693C87.182 21.806 86.7897 20.4345 86.0054 19.5202C85.2211 18.6059 84.0446 18.1488 82.5411 18.1488C80.9723 18.1488 79.7304 18.7365 78.75 19.8468C77.8347 20.957 77.3773 22.4591 77.3773 24.4183V32.9735H74.632V9.46291H77.3773V18.6059C77.9655 17.6916 78.6846 16.9732 79.5997 16.4508C80.5147 15.863 81.6912 15.6018 82.9985 15.6018C85.0904 15.6018 86.7243 16.2549 88.0316 17.4957C89.2738 18.7365 89.9273 20.6958 89.9273 23.3734Z"
                            fill="#1C1C1C"/>
                        <path
                            d="M109.406 32.9082H107.903C106.792 32.9082 106.007 32.7122 105.55 32.2552C105.092 31.798 104.831 31.145 104.831 30.296C103.523 32.1897 101.628 33.1695 99.0785 33.1695C97.183 33.1695 95.6796 32.7122 94.503 31.8632C93.3265 31.0142 92.8035 29.7735 92.8035 28.206C92.8035 26.5082 93.3919 25.202 94.5684 24.2877C95.745 23.3734 97.4445 22.9162 99.6669 22.9162H104.7V21.7407C104.7 20.6305 104.308 19.7815 103.589 19.1284C102.87 18.4753 101.824 18.2141 100.451 18.2141C99.2746 18.2141 98.2942 18.4753 97.5099 18.9978C96.7254 19.5202 96.2677 20.2386 96.0719 21.0876H93.3265C93.5226 19.3896 94.2415 18.0835 95.5488 17.1692C96.8561 16.2549 98.4903 15.7324 100.582 15.7324C102.739 15.7324 104.438 16.2549 105.68 17.2998C106.922 18.3447 107.445 19.9121 107.445 21.8713V29.251C107.445 30.1 107.837 30.492 108.556 30.492H109.406V32.9082ZM99.4054 25.1367C96.8561 25.1367 95.6142 26.1162 95.6142 28.0102C95.6142 28.8592 95.9411 29.5122 96.5946 30.0347C97.2484 30.5572 98.1634 30.8185 99.34 30.8185C101.04 30.8185 102.347 30.3612 103.262 29.5122C104.242 28.6632 104.7 27.4877 104.7 25.9857V25.1367H99.4054Z"
                            fill="#1C1C1C"/>
                        <path
                            d="M112.674 13.9038C112.674 12.4017 113 11.2915 113.72 10.5731C114.438 9.85473 115.55 9.46291 117.118 9.46291H120.256V11.9446H117.249C116.595 11.9446 116.073 12.0752 115.811 12.4017C115.55 12.663 115.353 13.1854 115.353 13.8385V15.7977H120.191V18.2794H115.353V32.843H112.608V18.3447H109.536V15.863H112.608V13.9038H112.674Z"
                            fill="#1C1C1C"/>
                        <path
                            d="M126.533 9.46291H129.671V11.9446H126.664C126.01 11.9446 125.487 12.0752 125.226 12.4017C124.964 12.663 124.768 13.1854 124.768 13.8385V15.7977H129.605V18.2794H124.833V32.843H122.088V13.9038C122.088 12.4017 122.415 11.2915 123.134 10.5731C123.853 9.85473 124.964 9.46291 126.533 9.46291Z"
                            fill="#1C1C1C"/>
                        <path
                            d="M131.172 28.9282C130.453 27.622 130.126 26.12 130.126 24.3566C130.126 22.6587 130.453 21.0913 131.172 19.7851C131.891 18.479 132.871 17.4341 134.179 16.7157C135.486 15.9973 136.924 15.6055 138.558 15.6055C140.192 15.6055 141.696 15.9973 142.938 16.7157C144.245 17.4341 145.226 18.479 145.944 19.7851C146.664 21.0913 146.99 22.5933 146.99 24.3566C146.99 26.0547 146.664 27.622 145.944 28.9282C145.226 30.2342 144.245 31.2792 142.938 31.9975C141.631 32.716 140.192 33.1077 138.558 33.1077C136.924 33.1077 135.421 32.716 134.179 31.9975C132.871 31.2792 131.891 30.2342 131.172 28.9282ZM143.526 27.622C143.984 26.6425 144.245 25.5975 144.245 24.3566C144.245 23.1811 143.984 22.0709 143.526 21.0913C143.068 20.1117 142.415 19.3933 141.565 18.8708C140.715 18.3484 139.735 18.0871 138.624 18.0871C137.513 18.0871 136.532 18.3484 135.682 18.8708C134.833 19.3933 134.179 20.177 133.721 21.0913C133.264 22.0056 133.002 23.1158 133.002 24.3566C133.002 25.5322 133.264 26.6425 133.721 27.622C134.179 28.6017 134.833 29.32 135.682 29.8425C136.532 30.365 137.513 30.6262 138.624 30.6262C139.735 30.6262 140.715 30.365 141.565 29.8425C142.415 29.32 143.068 28.6017 143.526 27.622Z"
                            fill="#1C1C1C"/>
                        <path
                            d="M151.108 13.9038C151.108 12.4017 151.434 11.2915 152.153 10.5731C152.872 9.85473 153.984 9.46291 155.552 9.46291H158.69V11.9446H155.683C155.029 11.9446 154.506 12.0752 154.245 12.4017C153.984 12.663 153.788 13.1854 153.788 13.8385V15.7977H158.625V18.2794H153.788V32.843H151.042V18.3447H147.97V15.863H151.042V13.9038H151.108Z"
                            fill="#1C1C1C"/>
                        <path
                            d="M183.922 32.9108H181.765C180.915 32.9108 180.196 32.78 179.673 32.5188C179.15 32.2575 178.628 31.8658 178.235 31.278L177.974 30.9515C176.078 32.3883 173.856 33.1065 171.307 33.1065C169.15 33.1065 167.189 32.5843 165.489 31.5393C163.79 30.4943 162.482 29.1228 161.502 27.2943C160.522 25.4658 160.064 23.4411 160.064 21.1554C160.064 18.8696 160.522 16.8451 161.502 15.0165C162.482 13.1879 163.79 11.8164 165.489 10.7715C167.189 9.72655 169.15 9.2041 171.307 9.2041C173.464 9.2041 175.425 9.72655 177.124 10.7715C178.824 11.8164 180.131 13.1879 181.112 15.0165C182.092 16.8451 182.55 18.8696 182.55 21.1554C182.55 22.7227 182.353 24.1595 181.896 25.4658C181.438 26.7718 180.785 28.0125 180 29.0575C180.327 29.4495 180.654 29.776 180.981 29.9718C181.308 30.1678 181.7 30.233 182.223 30.233H184.053V32.9108H183.922ZM175.621 27.8168C175.294 27.4248 174.967 27.0983 174.706 26.9025C174.444 26.7065 174.052 26.6413 173.595 26.6413H171.634V23.9636H174.052C174.837 23.9636 175.425 24.0942 175.948 24.3554C176.47 24.6166 176.928 25.0085 177.32 25.5963L178.039 26.5758C178.954 25.0738 179.477 23.2452 179.477 21.1554C179.477 19.3921 179.15 17.7594 178.432 16.3879C177.712 15.0165 176.797 13.9062 175.555 13.1226C174.314 12.3389 172.875 11.947 171.307 11.947C169.673 11.947 168.3 12.3389 167.058 13.1226C165.816 13.9062 164.836 15.0165 164.182 16.3879C163.463 17.7594 163.136 19.3921 163.136 21.1554C163.136 22.9187 163.463 24.5513 164.182 25.9228C164.901 27.2943 165.816 28.4045 167.058 29.1883C168.3 29.9718 169.738 30.3638 171.307 30.3638C173.268 30.3638 174.902 29.776 176.34 28.6005L175.621 27.8168Z"
                            fill="#1C1C1C"/>
                        <path
                            d="M200.589 32.9095H198.236L197.844 30.6237C196.536 32.2565 194.706 33.1055 192.353 33.1055C190.327 33.1055 188.693 32.4522 187.451 31.2115C186.209 29.9707 185.62 28.0115 185.62 25.3337V15.8642H188.366V25.2032C188.366 26.9665 188.758 28.338 189.542 29.2522C190.327 30.1665 191.438 30.6237 192.81 30.6237C194.379 30.6237 195.621 30.036 196.471 28.9257C197.321 27.8155 197.778 26.3135 197.778 24.3542V15.8642H200.523V32.9095H200.589Z"
                            fill="#1C1C1C"/>
                        <path
                            d="M213.009 18.5388H211.637C210.003 18.5388 208.826 19.0613 208.107 20.1715C207.388 21.2817 207.061 22.5879 207.061 24.2205V32.9065H204.316V15.8612H206.669L207.061 18.4082C207.584 17.6245 208.172 16.9714 208.957 16.5143C209.741 16.0571 210.852 15.7959 212.225 15.7959H213.009V18.5388Z"
                            fill="#1C1C1C"/>
                        <path
                            d="M218.63 11.0357C218.63 11.5582 218.434 11.9501 218.107 12.2766C217.78 12.6031 217.323 12.799 216.865 12.799C216.408 12.799 215.95 12.6031 215.623 12.2766C215.296 11.9501 215.1 11.4929 215.1 11.0357C215.1 10.5786 215.296 10.1214 215.623 9.79492C215.95 9.46837 216.408 9.27245 216.865 9.27245C217.323 9.27245 217.78 9.46837 218.107 9.79492C218.499 10.1214 218.63 10.5133 218.63 11.0357ZM218.238 15.8685V32.9137H215.492V15.8685H218.238Z"
                            fill="#1C1C1C"/>
                        <path d="M224.775 9.46291V32.9083H222.029V9.46291H224.775Z" fill="#1C1C1C"/>
                        <path
                            d="M231.636 11.0357C231.636 11.5582 231.44 11.9501 231.113 12.2766C230.787 12.6031 230.329 12.799 229.872 12.799C229.414 12.799 228.956 12.6031 228.63 12.2766C228.303 11.9501 228.107 11.4929 228.107 11.0357C228.107 10.5786 228.303 10.1214 228.63 9.79492C228.956 9.46837 229.414 9.27245 229.872 9.27245C230.329 9.27245 230.787 9.46837 231.113 9.79492C231.506 10.1214 231.636 10.5133 231.636 11.0357ZM231.244 15.8685V32.9137H228.499V15.8685H231.244Z"
                            fill="#1C1C1C"/>
                        <path
                            d="M238.501 29.7772C239.351 30.4302 240.462 30.7567 241.835 30.7567C243.011 30.7567 243.992 30.4955 244.776 30.0385C245.495 29.5812 245.887 28.9282 245.887 28.1445C245.887 27.4915 245.691 26.969 245.365 26.577C245.038 26.2505 244.515 25.9892 243.992 25.8587C243.404 25.728 242.619 25.5975 241.574 25.467C240.07 25.271 238.894 25.075 237.978 24.7485C237.063 24.4219 236.279 23.9648 235.691 23.377C235.102 22.724 234.841 21.875 234.841 20.7647C234.841 19.7851 235.102 18.8708 235.691 18.0871C236.279 17.3034 237.063 16.7157 238.044 16.2585C239.024 15.8014 240.135 15.6055 241.377 15.6055C243.404 15.6055 245.038 16.0626 246.28 17.0422C247.522 17.9565 248.241 19.328 248.371 21.026H245.626C245.495 20.1117 245.103 19.3933 244.319 18.8055C243.534 18.2178 242.619 17.9565 241.443 17.9565C240.266 17.9565 239.351 18.2178 238.632 18.6749C237.913 19.1321 237.586 19.7851 237.586 20.5688C237.586 21.1566 237.782 21.6137 238.109 21.875C238.436 22.1362 238.894 22.3974 239.416 22.528C239.939 22.6586 240.724 22.7893 241.835 22.9199C243.338 23.1158 244.58 23.3117 245.561 23.6383C246.541 23.9648 247.326 24.4219 247.914 25.1402C248.502 25.8587 248.829 26.773 248.829 27.9485C248.829 28.9935 248.502 29.9077 247.914 30.6915C247.326 31.4752 246.476 32.063 245.495 32.52C244.515 32.9772 243.338 33.1732 242.096 33.1732C239.874 33.1732 238.044 32.6507 236.736 31.6057C235.364 30.5607 234.71 29.124 234.645 27.2955H237.39C237.194 28.3405 237.652 29.124 238.501 29.7772Z"
                            fill="#1C1C1C"/>
                        <path
                            d="M267 23.3734V32.9083H264.255V23.5693C264.255 21.806 263.863 20.4345 263.08 19.5202C262.294 18.6059 261.119 18.1488 259.614 18.1488C258.047 18.1488 256.804 18.7365 255.823 19.8468C254.908 20.957 254.451 22.4591 254.451 24.4183V32.9735H251.706V9.46291H254.451V18.6059C255.04 17.6916 255.759 16.9732 256.672 16.4508C257.588 15.863 258.765 15.6018 260.073 15.6018C262.164 15.6018 263.799 16.2549 265.106 17.4957C266.411 18.7365 267 20.6958 267 23.3734Z"
                            fill="#1C1C1C"/>
                        <path d="M6.44181 8.82077V3.33495H0.951172V8.82077H6.44181Z" fill="#007AFF"/>
                        <path d="M36.6975 8.82564V3.33984H31.207V8.82564H36.6975Z" fill="#007AFF"/>
                        <path d="M22.5506 9.76925V2.32422L15.099 2.32422V9.76925H22.5506Z" fill="#007AFF"/>
                        <path d="M7.45062 24.9158V17.4707H-0.000976562V24.9158H7.45062Z" fill="#007AFF"/>
                        <path d="M37.7089 24.9206V17.4756H30.2573V24.9206H37.7089Z" fill="#007AFF"/>
                        <path d="M22.5798 24.8815V17.4365H15.1282V24.8815H22.5798Z" fill="#007AFF"/>
                        <path d="M6.43449 39.0455V33.5595H0.943848V39.0455H6.43449Z" fill="#007AFF"/>
                        <path d="M36.6927 39.0553V33.5693H31.202V39.0553H36.6927Z" fill="#007AFF"/>
                        <path d="M22.5433 39.9988V32.5537H15.0917V39.9988H22.5433Z" fill="#007AFF"/>
                        <path
                            d="M33.5122 21.1608L18.9359 6.66257L4.42486 21.1608L3.24829 19.9853L18.9359 4.31152L34.6888 19.9853L33.5122 21.1608Z"
                            fill="#007AFF"/>
                        <path
                            d="M33.3807 36.9L18.8043 22.4017L4.29327 36.9L3.1167 35.7245L18.8043 19.9854L34.5573 35.7245L33.3807 36.9Z"
                            fill="#007AFF"/>
                    </g>
                    <defs>
                        <clipPath id="clip0_17_2">
                            <rect width="267" height="40" fill="white"/>
                        </clipPath>
                    </defs>
                </svg>') }}"  />

            </td>
        </tr>
        </tbody>
    </table>

</div>
</body>
</html>
