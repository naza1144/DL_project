"""
URL configuration for dashboard app.
"""
from django.urls import path
from . import views

urlpatterns = [
    path('', views.index, name='index'),
    path('train_stream/', views.train_stream, name='train_stream'),
    path('gateway_stream/', views.gateway_stream, name='gateway_stream'),
    path('api/inspect/', views.inspect_api, name='inspect_api'),
]
