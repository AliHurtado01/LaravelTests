<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarea Compartida</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #4F46E5; /* Color índigo de Tailwind */
            color: white;
            padding: 10px 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            padding: 20px;
            color: #333;
        }
        .task-box {
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 6px;
            margin-top: 15px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
        .button {
            display: inline-block;
            background-color: #4F46E5;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Tienes una nueva tarea!</h1>
        </div>
        <div class="content">
            <p>Hola,</p>
            <p>Se ha compartido una tarea contigo en la aplicación de Gestión de Tareas.</p>
            
            <div class="task-box">
                <h2 style="margin-top: 0;">{{ $task->title }}</h2>
                <p>{{ $task->description }}</p>
            </div>

            <p style="text-align: center;">
                <a href="{{ url('/dashboard') }}" class="button">Ver Tarea en la App</a>
            </p>
        </div>
        <div class="footer">
            <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
        </div>
    </div>
</body>
</html>
