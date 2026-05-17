import re
import os

files = [
    '/Users/giovannavilchis/Herd/back3.6/resources/views/vendor-views/auth/register-step-2.blade.php',
    '/Users/giovannavilchis/Herd/back3.6/resources/views/vendor-views/auth/register-subscription-payment.blade.php',
    '/Users/giovannavilchis/Herd/back3.6/resources/views/vendor-views/auth/register-complete.blade.php',
    '/Users/giovannavilchis/Herd/back3.6/resources/views/vendor-views/auth/complete-failed.blade.php'
]

replacements = {
    # register-complete.blade.php
    r"translate\('General Info'\)": "'Información General'",
    r"translate\('Business Plan'\)": "'Plan de Negocios'",
    r"translate\('Complete'\)": "'Completado'",
    r"translate\('Transaction Failed!'\)": "'¡Transacción Fallida!'",
    r"translate\('Congratulations!'\)": "'¡Felicidades!'",
    r"translate\('You’ve opted for our commission-based plan\. Admin will review the details and activate your account shortly\. To explore the site\.'\)": "'Has optado por nuestro plan basado en comisiones. El administrador revisará los detalles y activará tu cuenta en breve. Para explorar el sitio:'",
    r"translate\('visit_here'\)": "'Visita aquí'",
    r"translate\('Sorry, Your Transaction can’t be completed\. Please choose another payment method\.'\)": "'Lo sentimos, tu transacción no pudo ser completada. Por favor, elige otro método de pago.'",
    r"translate\('Try_again'\)": "'Inténtalo de nuevo'",
    r"translate\('Thank you for your subscription purchase! Your payment was successfully processed\. Please note that your subscription will be activated once it has been approved by our Admin Team\. To explore the site'\)": "'¡Gracias por tu suscripción! Tu pago se procesó exitosamente. Ten en cuenta que tu suscripción se activará una vez que sea aprobada por nuestro equipo. Para explorar el sitio:'",
    
    # register-step-2.blade.php
    r"translate\('messages\.vendor'\)": "'Restaurante/Tienda'",
    r"translate\('application'\)": "'Registro'",
    r"translate\('Choose Your Business Plan'\)": "'Elige tu Plan de Negocios'",
    r"translate\('Commision_Base'\)": "'Basado en Comisión'",
    r"translate\('Vendor will pay'\)": "'El negocio pagará'",
    r"translate\('commission to'\)": "'de comisión a'",
    r"translate\('from each order\. You will get access of all the features and options  in vendor panel , app and interaction with user\.'\)": "'por cada pedido. Tendrás acceso a todas las funciones del panel, la app y podrás interactuar con los clientes.'",
    r"translate\('Subscription_Base'\)": "'Basado en Suscripción'",
    r"translate\('Run vendor by puchasing subsciption packages\. You will have access the features of in vendor panel , app and interaction with user according to the subscription packages\.'\)": "'Opera tu negocio comprando paquetes de suscripción. Tendrás acceso a las funciones del panel y app según tu paquete.'",
    r"translate\('Choose Subscription Package'\)": "'Elige un Paquete de Suscripción'",
    r"translate\('messages\.days'\)": "'días'",
    r"translate\('messages\.POS'\)": "'Punto de Venta (POS)'",
    r"translate\('messages\.mobile_app'\)": "'App Móvil'",
    r"translate\('messages\.chatting_options'\)": "'Opciones de Chat'",
    r"translate\('messages\.review_section'\)": "'Sección de Reseñas'",
    r"translate\('messages\.self_delivery'\)": "'Repartidores Propios'",
    r"translate\('messages\.Unlimited_trips'\)": "'Viajes Ilimitados'",
    r"translate\('messages\.Unlimited_Orders'\)": "'Pedidos Ilimitados'",
    r"translate\('messages\.trips'\)": "'Viajes'",
    r"translate\('messages\.Orders'\)": "'Pedidos'",
    r"translate\('messages\.Unlimited_uploads'\)": "'Subidas Ilimitadas'",
    r"translate\('messages\.uploads'\)": "'Subidas'",
    r"translate\('Next'\)": "'Siguiente'",
    
    # register-subscription-payment.blade.php
    r"translate\('Payment'\)": "'Pago'",
    r"translate\('Pay_Via_Online'\)": "'Pagar en línea'",
    r"translate\('Free_Trial'\)": "'Prueba Gratuita'",
    r"translate\('Pay'\)": "'Pagar'",
    r"translate\('Back'\)": "'Atrás'",
    r"translate\('months'\)": "'meses'",
    r"translate\('year'\)": "'año'",
    r"translate\('days_free_trial'\)": "'días de prueba gratis'",
}

for file_path in files:
    if os.path.exists(file_path):
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        for pattern, rep in replacements.items():
            content = re.sub(pattern, rep, content)
            
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(content)

print("Translation replacements complete for all steps.")
