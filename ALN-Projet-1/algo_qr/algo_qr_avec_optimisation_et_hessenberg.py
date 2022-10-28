import math
import numpy as np
import scipy.linalg as nla

def _Hessenberg_ (M):
    # Algorithme de mise sous forme de Hessenberg non fonctionnel
    # M est une matrice de données carrée
    
    m = M.shape[0]
    H = M.copy()
    Q = np.eye(m)
    for k in range(m-2):
        x = H[k+1:m, k]
        v = x.copy()
        if v[0] >= 0:
            v[0] += nla.norm(x, 2)
        else:
            v[0] -= nla.norm(x, 2)
        F = np.eye(m-k-1) - 2/np.dot(v,v)*np.outer(v,v)
        Qk = np.eye(m)
        Qk[k+1:m, k+1:m] = F
        H = np.dot(np.dot(Qk, H), Qk)
        H[k+2:m, k] = 0
        Q = np.dot(Qk, Q)
    return H

def _VPAlgoQR_(A):
    # Algorithme QR Optimisé
    # A est une matrice de données carrée
    
    M = A.shape[0]
    lamb = list(0 for i in range(M))
    epsilon = 1e-10
    A = _Hessenberg_(A)
    k1 = M-1
    while(k1 >= 0):
        k0 = k1
        while (k0 >= 1 and abs(A[k0, k0-1]) > epsilon):
            k0 -= 1
        if (k0 == k1):
            lamb[k1] = A[k1, k1]
            k1 -= 1
        else:
            mu = A[k1, k1]
            for i in range(k0, k1+1):
                A[i, i] -= mu
            Q = nla.qr(A[k0:k1+1, k0:k1+1])[0]
            R = nla.qr(A[k0:k1+1, k0:k1+1])[1]
            A[k0:k1+1, k0:k1+1] = np.dot(R, Q)
            for i in range(k0, k1+1):
                A[i, i] += mu
    return lamb

A = np.array([[2, -18, -38, -20], [-18, 2, -20, -38], [-38 , -20, 2, -18], [-20, -38, -18, 2]])
B = np.array([[1, 0, 0, 0], [0, 2, 0, 0], [0 , 0, 3, 0], [0, 0, 0, 4]])
C = np.array([[3, 1/2, 1], [4, 8, -4], [3, 7/2, 1]])