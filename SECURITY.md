# Seguridad

## Versiones soportadas

| Versión | Soporte |
| --- | --- |
| 0.1.x | Sí |

No hay canal LTS. Las correcciones van a la rama de la versión menor publicada.

## Qué cubre este plugin

Endurecimiento de superficie: XML-RPC, fugas de versión, editor de archivos, throttle de login, cabeceras HTTP, enumeración REST de usuarios y contraseñas de aplicación. No es un producto de detección de amenazas.

## Qué no reportar aquí

- Problemas de configuración del propio WordPress, del alojamiento o de un tema de terceros, salvo que el plugin los agrave.
- Fuerza bruta que el throttle no puede contener (IPs rotatorias, botnets). Eso no es una vulnerabilidad del plugin.
- Ausencia de WAF, escáner de malware o 2FA. Está documentado como fuera de alcance.

## Cómo informar de una vulnerabilidad

1. Use **GitHub Security Advisories** en el repositorio (informe privado). No abra un issue público con detalles explotables.
2. Incluya versión del plugin, versión de WordPress y PHP, pasos de reproducción y el impacto.
3. No adjunte exploits genéricos contra WordPress core; ciña el informe a este plugin.

No hay programa de recompensas.

## Divulgación

Tras confirmar el problema se publicará un aviso en `CHANGELOG.md` y, si aplica, una versión parcheada. No se discuten detalles explotables hasta que exista corrección o una mitigación clara.
